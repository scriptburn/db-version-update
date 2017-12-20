<?php
namespace ScriptBurn;

class UpdateDb
{
    protected $settings, $pdo, $versionData;

    public function __construct(\ScriptBurn\Setting\Setting $settings, $pdo, $versionData = null)
    {
        $this->settings = $settings;
        $this->pdo      = $pdo;

        $this->parseVersionData($versionData);

    }
    private function parseVersionData($versionData)
    {
        if (!is_null($versionData) && is_array($versionData) && !empty($versionData['type']))
        {
            if ($versionData['type'] == 'composer')
            {
                if (empty($versionData['path']))
                {
                    throw new \Exception("composer path provided in version data");

                }
                elseif (!file_Exists($versionData['path']))
                {
                    throw new \Exception(" composer.json does not exists with path:{$versionData['path']} ");
                }
                elseif ((!($composer = @json_decode(file_get_contents($versionData['path'])))))
                {
                    throw new \Exception("composer.json invalid format");
                }
                elseif (!property_exists($composer, 'dbversion'))
                {
                    throw new \Exception("no dbversion in composer.json");
                }
                elseif ((string) $composer->dbversion == "")
                {
                    throw new \Exception("invalid dbversion in composer.json");
                }
                else
                {
                    $this->versionData = ['type' => $versionData['type'], 'version' => (int) $composer->dbversion];
                }
            }
            else
            {
                throw new \Exception("version data passed with unknown type");
            }
        }
    }
    private function update($package, $current_db_ver, $target_db_ver)
    {
        // no PHP timeout for running updates
        //set_time_limit(0);

        $db_updated = false;

        p_l("$current_db_ver < $target_db_ver");
        //$loop;
        while ($current_db_ver < $target_db_ver)
        {

            // increment the current db_ver by one
            $current_db_ver++;

            // each db version will require a separate update function
            $method = "update_routine_{$current_db_ver}";
            p_l($method);
            if (method_exists($this, $method))
            {
                p_l("calling $method");
                $ret = call_user_func_array(array($this, $method), []);
                if ($ret !== true)
                {
                    p_l("$method failed:$ret");
                    return;
                }
                if ($ret && !$db_updated)
                {
                    $db_updated = true;
                }
                $this->settings->set($package . '_db_ver', $current_db_ver);

            }
            else
            {
                $this->settings->set($package . '_db_ver', $current_db_ver);
            }

            // update the option in the database, so that this process can always
            // pick up where it left off

            // $this->settings->set($package . '_db_ver', $current_db_ver);
        }
        return $db_updated;
    }

    public function maybeUpdate($package, $target_db_ver = null)
    {
        if (is_null($target_db_ver))
        {
            if (!is_array($this->versionData) || empty($this->versionData['version']))
            {
                throw new \Exception('You must provide target db version');

            }
            else
            {
                $target_db_ver = $this->versionData['version'];
            }
        }

        $current_db_ver = (int) $this->settings->get($package . '_db_ver');
        if ($current_db_ver >= $target_db_ver)
        {
            p_l("Db already uptodate $current_db_ver >= $target_db_ver");
            return;
        }

        $ret = $this->update($package, $current_db_ver, $target_db_ver);

    }

    public function tableExists($table)
    {
        try
        {
            $result = $this->pdo->query("select 1 from $table limit 1");
            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    public function execute($sqls)
    {
        $sqls = is_array($sqls) ? $sqls : [$sqls];
        foreach ($sqls as $sql)
        {
            $this->pdo->query($sql);

        }
    }

}
