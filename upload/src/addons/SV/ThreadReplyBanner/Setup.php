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

    public function upgrade2040001Step1()
    {
        $this->installStep1();
    }

    public function upgrade2040001Step2()
    {
        $this->installStep2();
    }

    public function upgrade2040001Step3()
    {
        $this->query("
            UPDATE xf_moderator_log 
            SET content_type = 'sv_thread_banner', discussion_content_type = 'thread'
            WHERE content_type = 'thread_banner'
        ");
    }

    public function upgrade2040001Step4()
    {
        $this->query("
            UPDATE xf_edit_history 
            SET content_type = 'sv_thread_banner' 
            WHERE content_type = 'thread_banner'
        ");
    }

    public function upgrade2040001Step5()
    {
        $this->query("
            UPDATE xf_moderator_log 
            SET discussion_content_type = 'thread'
            WHERE content_type = 'sv_thread_banner' and discussion_content_type = 'thread_banner'
        ");
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

        $bannerSchema = function (string $primaryKey, $table)
        {
            /** @var Create|Alter $table */
            $this->addOrChangeColumn($table, $primaryKey)->type('int');
            $table->addPrimaryKey($primaryKey);

            $this->addOrChangeColumn($table, 'raw_text')->type('mediumtext');
            $this->addOrChangeColumn($table, 'banner_state')->type('tinyint')->length(1)->setDefault(1);
            $this->addOrChangeColumn($table, 'banner_user_id')->type('int')->setDefault(0);
            $this->addOrChangeColumn($table, 'banner_edit_count')->type('int')->setDefault(0);
            $this->addOrChangeColumn($table, 'banner_last_edit_date')->type('int')->setDefault(0);
            $this->addOrChangeColumn($table, 'banner_last_edit_user_id')->type('int')->setDefault(0);
        };

        $tables['xf_sv_thread_banner'] = function ($table) use ($bannerSchema)
        {
            $bannerSchema('thread_id', $table);
        };

        $tables['xf_sv_forum_banner'] = function ($table) use ($bannerSchema)
        {
            $bannerSchema('node_id', $table);
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