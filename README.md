

## Package to update db structure acording to a target package version
--------

##Usage:

Create a child class like this (A sample child class has been provided as **sampleDbUpdateService.php**):
<code>class DbUpdateService extends \Scriptburn\UpdateDb
{
	
	// will be called for target db version 1
    public function update_routine_1()
    {
        /* make sure to return true
        other wise db update will stop propogating
        and update process will run again
         */
        return true;
    }
    // will be called for target db version 2
    public function update_routine_2()
    {
        /* make sure to return true
        other wise db update will stop propogating
        and update process will run again
         */
        return true;
    }
}</code>

Next we will Call the actual function that checks if db update is needed. Best place for this code will be in a middleware
<code>// make sure composer.json has a key named **dbversion** in it
$optionalVersionData=['type' => 'composer', 'path' => "/path/to/composer.json"];</code>

<code>$dbUpdateCheck = new App\Services\DbUpdateService(< instance of \ScriptBurn\Settings >, < instance of pdo connection >, $optionalVersionData);</code>

 <code>//If you passed $optionalVersionData as 3rd parameter to `App\Services\DbUpdateService` constructor you do not need to pass current db version to method  maybeUpdate as second parameter  $dbUpdateCheck->maybeUpdate('scriptburn/git-auto-deploy',< optional current db version no >');
        </code>

