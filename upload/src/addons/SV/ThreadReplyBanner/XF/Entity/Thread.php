<?php

namespace SV\ThreadReplyBanner\XF\Entity;

use XF\Mvc\Entity\Structure;

/*
 * Extends \XF\Entity\Thread
 *
 * COLUMNS
 * @property bool has_banner
 *
 * RELATIONS
 * @property \SV\ThreadReplyBanner\Entity\ThreadBanner ThreadBanner
 */
class Thread extends XFCP_Thread
{
    /**
     * @param string|null $error
     * @return bool
     */
    public function canManageThreadReplyBanner(/** @noinspection PhpUnusedParameterInspection */&$error = null)
    {
        return \XF::visitor()->hasNodePermission(
            $this->node_id,
            'sv_replybanner_manage'
        );
    }

    public function canViewBanner()
    {
        return (
                   \XF::visitor()->hasPermission('forum', 'sv_replybanner_show') ||
                   \XF::visitor()->hasPermission('forum', 'sv_replybanner_manage')
               ) && $this->ThreadBanner && $this->ThreadBanner->banner_state;
    }

    public function getThreadBanner()
    {
        if ($this->has_banner)
        {
            return [
                'title'            => '',
                'message'          => $this->ThreadBanner->getRenderedBannerText(),
                'active'           => true,
                'display_order'    => 1,
                'dismissible'      => false,
                'user_criteria'    => [],
                'page_criteria'    => [],
                'notice_type'      => 'scrolling',
                'display_style'    => 'primary',
                'css_class'        => '',
                'display_duration' => 0,
                'delay_duration'   => 0,
                'auto_dismiss'     => 0
            ];
        }
        else
        {
            return null;
        }
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->columns['has_banner'] = ['type' => self::BOOL, 'default' => false];

        $structure->relations['ThreadBanner'] = [
            'entity'     => 'SV\ThreadReplyBanner:ThreadBanner',
            'type'       => self::TO_ONE,
            'conditions' => 'thread_id',
            'primary'    => true,
        ];

        return $structure;
    }
}
