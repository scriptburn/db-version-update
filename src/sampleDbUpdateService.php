<?php

class DbUpdateService extends \Scriptburn\UpdateDb
{
    // will be called for target db version 1
    public function update_routine_1()
    {
        $user_table[] = "CREATE TABLE `users` (
  `id` bigint(10) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $user_table[] = "ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);";
        $user_table[] = " ALTER TABLE `users`
  MODIFY `id` bigint(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;";

        if (!$this->tableExists('users'))
        {
            $this->execute($user_table);
        }

        return true;
    }

    // will be called for target db version 2
    public function update_routine_2()
    {
        $this->pdo->query("ALTER TABLE `users` ADD `role` varchar(30) DEFAULT NULL AFTER `password`");
        return true;
    }

}
