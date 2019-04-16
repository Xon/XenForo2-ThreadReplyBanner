<?php

namespace SV\ThreadReplyBanner\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
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
        return \XF::visitor()->hasNodePermission($this->node_id, 'sv_replybanner_manage');
    }

    /**
     * @param string|null $error
     * @return bool
     */
    public function canViewBanner(/** @noinspection PhpUnusedParameterInspection */&$error = null)
    {
        if (!$this->has_banner)
        {
            return false;
        }
        $visitor = \XF::visitor();

        if (!$visitor->hasPermission('forum', 'sv_replybanner_show') &&
            !$visitor->hasPermission('forum', 'sv_replybanner_manage'))
        {
            return false;
        }

        return $this->ThreadBanner && $this->ThreadBanner->banner_state;
    }

    /**
     * @return array
     */
    public function getThreadBanner()
    {
        $threadBanner = $this->ThreadBanner;
        if (!$threadBanner || !$threadBanner->banner_state)
        {
            return [];
        }
        return [
            'title'            => '',
            'message'          => $threadBanner->getRenderedBannerText(),
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

    /**
     * @throws \XF\PrintableException
     */
    protected function _postDelete()
    {
        parent::_postDelete();
        if ($this->ThreadBanner)
        {
            $this->ThreadBanner->delete();
        }
    }

    /**
     * @param Structure $structure
     *
     * @return Structure
     */
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
