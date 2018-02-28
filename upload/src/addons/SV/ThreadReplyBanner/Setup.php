<?php

namespace SV\ThreadReplyBanner;

use XF\AddOn\AbstractSetup;
use XF\AddOn\StepRunnerInstallTrait;
use XF\AddOn\StepRunnerUninstallTrait;
use XF\AddOn\StepRunnerUpgradeTrait;
use XF\Db\Schema\Alter;
use XF\Db\Schema\Create;

class Setup extends AbstractSetup
{
	use StepRunnerInstallTrait;
	use StepRunnerUpgradeTrait;
	use StepRunnerUninstallTrait;

	public function installStep1() {
		$this->schemaManager()->alterTable('xf_thread', function(Alter $table) {
			$table->addColumn('has_banner')->type('tinyint')->setDefault(0);
		});
		$this->schemaManager()->createTable('xf_thread_banner', function(Create $table) {
			$table->addColumn('thread_id')->type('uint')->primaryKey();
			$table->addColumn('raw_text')->type('mediumtext');
			$table->addColumn('banner_state')->type('tinyint')->length(3)->setDefault(1);
			$table->addColumn('banner_user_id')->type('int')->setDefault(0);
			$table->addColumn('banner_edit_count')->type('int')->setDefault(0);
			$table->addColumn('banner_last_edit_date')->type('int')->setDefault(0);
			$table->addColumn('banner_last_edit_user_id')->type('int')->setDefault(0);
		});

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

	public function upgrade1000402Step1() {
		// clean-up orphaned thread banners.
		$this->db()->query("
            DELETE
            FROM xf_thread_banner
            WHERE NOT EXISTS (SELECT thread_id FROM xf_thread)
        ");
	}

	public function uninstallStep1(){
		$this->schemaManager()->dropTable('xf_thread_banner');

		$this->db()->query("
            DELETE FROM xf_permission_entry
            WHERE permission_group_id = 'forum' 
            AND permission_id IN ('sv_replybanner_show', 'sv_replybanner_manage')
        ");

		$this->schemaManager()->alterTable('xf_thread', function(Alter $table) {
			$table->dropColumns(['has_banner']);
		});
	}
}