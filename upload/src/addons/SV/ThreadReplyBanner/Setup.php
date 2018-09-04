<?php

namespace SV\ThreadReplyBanner;

use SV\Utils\InstallerHelper;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
    // from https://github.com/Xon/XenForo2-Utils cloned to src/addons/SV/Utils
    use InstallerHelper;
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1()
    {
        $sm = $this->schemaManager();

        foreach ($this->getTables() as $tableName => $callback)
        {
            $sm->createTable($tableName, $callback);
            $sm->alterTable($tableName, $callback);
        }
    }

    public function installStep2()
    {
        $sm = $this->schemaManager();

        foreach ($this->getAlterTables() as $tableName => $callback)
        {
            $sm->alterTable($tableName, $callback);
        }
    }

    public function installStep3()
    {
        $this->db()->query("
	        INSERT IGNORE INTO xf_permission_entry (user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
	            SELECT DISTINCT user_group_id, user_id, convert(permission_group_id USING utf8), 'sv_replybanner_show', permission_value, permission_value_int
	            FROM xf_permission_entry
	            WHERE permission_group_id = 'forum' AND  permission_id IN ('postReply')
        ");

        $this->db()->query("
            INSERT IGNORE INTO xf_permission_entry (user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
                SELECT DISTINCT user_group_id, user_id, convert(permission_group_id USING utf8), 'sv_replybanner_manage', permission_value, permission_value_int
                FROM xf_permission_entry
                WHERE permission_group_id = 'forum' AND permission_id IN ('warn','editAnyPost','deleteAnyPost')
    	");
    }

    public function upgrade2000070Step3()
    {
        // clean-up orphaned thread banners.
        $this->db()->query("
            DELETE
            FROM xf_thread_banner
            WHERE NOT EXISTS (SELECT thread_id FROM xf_thread)
        ");
    }

    public function upgrade2000070Step1()
    {
        $this->installStep1();

        $sm = $this->schemaManager();

        foreach ($this->getTables() as $tableName => $callback)
        {
            $sm->alterTable($tableName, $callback);
        }
    }

    public function upgrade2000070Step2()
    {
        $this->installStep2();
    }

    public function uninstallStep1()
    {
        $sm = $this->schemaManager();

        foreach ($this->getTables() as $tableName => $callback)
        {
            $sm->dropTable($tableName);
        }
    }

    public function uninstallStep2()
    {
        $sm = $this->schemaManager();

        foreach ($this->getRemoveAlterTables() as $tableName => $callback)
        {
            $sm->alterTable($tableName, $callback);
        }
    }

    public function uninstallStep3()
    {
        $this->db()->query("
            DELETE FROM xf_permission_entry
            WHERE permission_group_id = 'forum' 
            AND permission_id IN ('sv_replybanner_show', 'sv_replybanner_manage')
        ");
    }

    /**
     * @return array
     */
    protected function getTables()
    {
        $tables = [];

        $tables['xf_thread_banner'] = function ($table) {
            /** @var Create|Alter $table */
            if ($table instanceof Create)
            {
                $table->checkExists(true);
            }

            $this->addOrChangeColumn($table, 'thread_id')->type('int');
            $this->addOrChangeColumn($table, 'raw_text')->type('mediumtext');
            $this->addOrChangeColumn($table, 'banner_state')->type('tinyint')->length(3)->setDefault(1);
            $this->addOrChangeColumn($table, 'banner_user_id')->type('int')->setDefault(0);
            $this->addOrChangeColumn($table, 'banner_edit_count')->type('int')->setDefault(0);
            $this->addOrChangeColumn($table, 'banner_last_edit_date')->type('int')->setDefault(0);
            $this->addOrChangeColumn($table, 'banner_last_edit_user_id')->type('int')->setDefault(0);

            $table->addPrimaryKey('thread_id');
        };

        return $tables;
    }

    /**
     * @return array
     */
    protected function getAlterTables()
    {
        $tables = [];

        $tables['xf_thread'] = function (Alter $table) {
            $this->addOrChangeColumn($table, 'has_banner')->type('tinyint')->setDefault(0);
        };

        return $tables;
    }

    protected function getRemoveAlterTables()
    {
        $tables = [];

        $tables['xf_thread'] = function (Alter $table) {
            $table->dropColumns('has_banner');
        };

        return $tables;
    }
}