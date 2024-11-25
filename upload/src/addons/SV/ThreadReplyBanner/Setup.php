<?php

namespace SV\ThreadReplyBanner;

use SV\StandardLib\InstallerHelper;
use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
    use InstallerHelper;
    use StepRunnerInstallTrait;
    use StepRunnerUpgradeTrait;
    use StepRunnerUninstallTrait;

    public function installStep1(): void
    {
        $sm = $this->schemaManager();

        foreach ($this->getTables() as $tableName => $callback)
        {
            $sm->createTable($tableName, $callback);
            $sm->alterTable($tableName, $callback);
        }
    }

    public function installStep2(): void
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

    public function installStep3(): void
    {
        $db = $this->db();
        $db->query("
	        INSERT IGNORE INTO xf_permission_entry (user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
	            SELECT DISTINCT user_group_id, user_id, convert(permission_group_id USING utf8), 'sv_replybanner_show', permission_value, permission_value_int
	            FROM xf_permission_entry
	            WHERE permission_group_id = 'forum' AND  permission_id IN ('postReply')
        ");

        $db->query("
            INSERT IGNORE INTO xf_permission_entry (user_group_id, user_id, permission_group_id, permission_id, permission_value, permission_value_int)
                SELECT DISTINCT user_group_id, user_id, convert(permission_group_id USING utf8), 'sv_replybanner_manage', permission_value, permission_value_int
                FROM xf_permission_entry
                WHERE permission_group_id = 'forum' AND permission_id IN ('warn','editAnyPost','deleteAnyPost')
    	");
    }

    public function upgrade2010000Step3(): void
    {
        // clean-up orphaned thread banners.
        \XF::db()->query('
            DELETE
            FROM xf_sv_thread_banner
            WHERE NOT EXISTS (SELECT thread_id FROM xf_thread)
        ');
    }

    public function upgrade2040001Step1(): void
    {
        $this->installStep1();
    }

    public function upgrade2040001Step2(): void
    {
        $this->installStep2();
    }

    public function upgrade2040001Step3(): void
    {
        $this->query("
            UPDATE xf_moderator_log 
            SET content_type = 'sv_thread_banner', discussion_content_type = 'thread'
            WHERE content_type = 'thread_banner'
        ");
    }

    public function upgrade2040001Step4(): void
    {
        $this->query("
            UPDATE xf_edit_history 
            SET content_type = 'sv_thread_banner' 
            WHERE content_type = 'thread_banner'
        ");
    }

    public function upgrade2040001Step5(): void
    {
        $this->query("
            UPDATE xf_moderator_log 
            SET discussion_content_type = 'thread'
            WHERE content_type = 'sv_thread_banner' and discussion_content_type = 'thread_banner'
        ");
    }

    public function upgrade2040101Step1(): void
    {
        /** @noinspection SqlWithoutWhere */
        $this->query('
            DELETE FROM xf_sv_thread_banner WHERE banner_state = 0 AND LENGTH(raw_text) = 0 AND banner_edit_count = 0;
        ');
    }

    public function upgrade2040101Step2(): void
    {
        $this->query('
            DELETE FROM xf_sv_forum_banner WHERE banner_state = 0 AND LENGTH(raw_text) = 0 AND banner_edit_count = 0;
        ');
    }

    public function upgrade2040101Step3(): void
    {
        /** @noinspection SqlWithoutWhere */
        $this->query('
            UPDATE xf_thread AS thread
            LEFT JOIN xf_sv_thread_banner AS threadBanner ON thread.thread_id = threadBanner.thread_id
            SET thread.sv_has_thread_banner = if(threadBanner.banner_state = 1 and length(threadBanner.raw_text) > 0, 1, 0);
        ');
    }

    public function upgrade2040101Step4(): void
    {
        /** @noinspection SqlWithoutWhere */
        $this->query('
            UPDATE xf_forum AS forum
            LEFT JOIN xf_sv_forum_banner AS forumBanner ON forum.node_id = forumBanner.node_id
            SET forum.sv_has_forum_banner = if(forumBanner.banner_state = 1 and length(forumBanner.raw_text) > 0, 1, 0);
        ');
    }

    public function uninstallStep1(): void
    {
        $sm = $this->schemaManager();

        foreach ($this->getTables() as $tableName => $callback)
        {
            $sm->dropTable($tableName);
        }
    }

    public function uninstallStep2(): void
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

    public function uninstallStep3(): void
    {
        \XF::db()->query("
            DELETE FROM xf_permission_entry
            WHERE permission_group_id = 'forum' 
            AND permission_id IN ('sv_replybanner_show', 'sv_replybanner_manage')
        ");
    }

    protected function getTables() : array
    {
        $tables = [];

        $this->migrateTable('xf_thread_banner', 'xf_sv_thread_banner');

        $bannerSchema = function (string $primaryKey, $table): void
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

        $tables['xf_sv_thread_banner'] = function ($table) use ($bannerSchema): void
        {
            $bannerSchema('thread_id', $table);
        };

        $tables['xf_sv_forum_banner'] = function ($table) use ($bannerSchema): void
        {
            $bannerSchema('node_id', $table);
        };

        return $tables;
    }

    protected function getAlterTables() : array
    {
        $tables = [];

        $tables['xf_thread'] = function (Alter $table): void
        {
            $this->addOrChangeColumn($table, 'sv_has_thread_banner', 'tinyint', 1, ['has_banner'])->setDefault(0);
        };

        $tables['xf_forum'] = function (Alter $table): void
        {
            $this->addOrChangeColumn($table, 'sv_has_forum_banner', 'tinyint', 1)->setDefault(0);
        };

        return $tables;
    }

    protected function getRemoveAlterTables() : array
    {
        $tables = [];

        $tables['xf_thread'] = function (Alter $table): void
        {
            $table->dropColumns('sv_has_thread_banner');
        };

        $tables['xf_forum'] = function (Alter $table): void
        {
            $table->dropColumns('sv_has_forum_banner');
        };

        return $tables;
    }
}