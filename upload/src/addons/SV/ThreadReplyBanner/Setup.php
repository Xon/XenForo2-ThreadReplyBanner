<?php

namespace SV\ThreadReplyBanner;

use SV\StandardLib\InstallerHelper;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

/**
 * Class Setup
 *
 * @package SV\ThreadReplyBanner
 */
class Setup extends AbstractSetup
{
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
            if ($sm->tableExists($tableName))
            {
                $sm->alterTable($tableName, $callback);
            }
        }
    }

    /**
     * @throws \XF\Db\Exception
     */
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

    /**
     * @throws \XF\Db\Exception
     */
    public function upgrade2010000Step3()
    {
        // clean-up orphaned thread banners.
        $this->db()->query('
            DELETE
            FROM xf_sv_thread_banner
            WHERE NOT EXISTS (SELECT thread_id FROM xf_thread)
        ');
    }

    public function upgrade2040000Step1()
    {
        $this->installStep1();
    }

    public function upgrade2040000Step2()
    {
        $this->installStep2();
    }

    public function upgrade2040000Step3()
    {
        $this->db()->update('xf_moderator_log', [
            'content_type' => 'sv_thread_banner'
        ], 'content_type = ?', 'thread_banner');
    }

    public function upgrade2040000Step4()
    {
        $this->db()->update('xf_edit_history', [
            'content_type' => 'sv_thread_banner'
        ], 'content_type = ?', 'thread_banner');
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
            if ($sm->tableExists($tableName))
            {
                $sm->alterTable($tableName, $callback);
            }
        }
    }

    /**
     * @throws \XF\Db\Exception
     */
    public function uninstallStep3()
    {
        $this->db()->query("
            DELETE FROM xf_permission_entry
            WHERE permission_group_id = 'forum' 
            AND permission_id IN ('sv_replybanner_show', 'sv_replybanner_manage')
        ");
    }

    protected function getTables() : array
    {
        $tables = [];

        $this->migrateTable('xf_thread_banner', 'xf_sv_thread_banner');

        $tables['xf_sv_thread_banner'] = function ($table)
        {
            /** @var Create|Alter $table */
            $this->addOrChangeColumn($table, 'thread_id')->type('int');
            $this->addOrChangeColumn($table, 'raw_text')->type('mediumtext');
            $this->addOrChangeColumn($table, 'banner_state')->type('tinyint')->length(1)->setDefault(1);
            $this->addOrChangeColumn($table, 'banner_user_id')->type('int')->setDefault(0);
            $this->addOrChangeColumn($table, 'banner_edit_count')->type('int')->setDefault(0);
            $this->addOrChangeColumn($table, 'banner_last_edit_date')->type('int')->setDefault(0);
            $this->addOrChangeColumn($table, 'banner_last_edit_user_id')->type('int')->setDefault(0);

            $table->addPrimaryKey('thread_id');
        };

        $tables['xf_sv_forum_banner'] = function ($table)
        {
            /** @var Create|Alter $table */
            $this->addOrChangeColumn($table, 'node_id')->type('int');
            $this->addOrChangeColumn($table, 'raw_text')->type('mediumtext');
            $this->addOrChangeColumn($table, 'banner_state')->type('tinyint')->length(1)->setDefault(1);
            $this->addOrChangeColumn($table, 'banner_user_id')->type('int')->setDefault(0);
            $this->addOrChangeColumn($table, 'banner_edit_count')->type('int')->setDefault(0);
            $this->addOrChangeColumn($table, 'banner_last_edit_date')->type('int')->setDefault(0);
            $this->addOrChangeColumn($table, 'banner_last_edit_user_id')->type('int')->setDefault(0);

            $table->addPrimaryKey('node_id');
        };

        return $tables;
    }

    protected function getAlterTables() : array
    {
        $tables = [];

        $tables['xf_thread'] = function (Alter $table)
        {
            $this->addOrChangeColumn($table, 'sv_has_thread_banner', 'tinyint', 1, ['has_banner'])->setDefault(0);
        };

        $tables['xf_forum'] = function (Alter $table)
        {
            $this->addOrChangeColumn($table, 'sv_has_forum_banner', 'tinyint', 1)->setDefault(0);
        };

        return $tables;
    }

    protected function getRemoveAlterTables() : array
    {
        $tables = [];

        $tables['xf_thread'] = function (Alter $table)
        {
            $table->dropColumns('sv_has_thread_banner');
        };

        $tables['xf_forum'] = function (Alter $table)
        {
            $table->dropColumns('sv_has_forum_banner');
        };

        return $tables;
    }
}