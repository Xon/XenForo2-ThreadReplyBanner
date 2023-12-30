<?php

namespace SV\ThreadReplyBanner\Service\ReplyBanner;

use SV\ThreadReplyBanner\Entity\AbstractBanner as AbstractBannerEntity;
use SV\ThreadReplyBanner\Entity\ContentBannerInterface as ContentBannerEntityInterface;
use SV\ThreadReplyBanner\Entity\ContentBannerTrait as ContentBannerEntityTrait;
use XF\Entity\User as UserEntity;
use XF\Http\Request as HttpRequest;
use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Repository;
use XF\Repository\EditHistory as EditHistoryRepo;
use XF\Service\AbstractService;
use XF\Service\ValidateAndSavableTrait;
use XF\App as BaseApp;
use function strlen;

class Manager extends AbstractService
{
    use ValidateAndSavableTrait;

    /**
     * @var Entity|ContentBannerEntityInterface|ContentBannerEntityTrait
     */
    protected $content;

    /**
     * @var AbstractBannerEntity
     */
    protected $replyBanner;
    /** @var bool */
    protected $logEdit = true;
    /** @var bool */
    protected $logHistory = true;
    /** @var string|null */
    protected $oldMessage;

    /**
     * Manager constructor.
     *
     * @param BaseApp $app
     * @param Entity|AbstractBannerEntity|ContentBannerEntityInterface $content
     */
    public function __construct(BaseApp $app, Entity $content)
    {
        if ($content instanceof ContentBannerEntityInterface)
        {
            $this->content = $content;
            $this->replyBanner = $content->getSvContentReplyBanner(true);
        }
        else if ($content instanceof AbstractBannerEntity)
        {
            $this->content = $content->getAssociatedContent();
            $this->replyBanner = $content;
        }

        parent::__construct($app);
    }

    /**
     * @return Entity|ContentBannerEntityInterface|ContentBannerEntityTrait
     */
    public function getContent() : Entity
    {
        return $this->content;
    }

    public function getReplyBanner() : AbstractBannerEntity
    {
        return $this->replyBanner;
    }

    public function setLogEdit(bool $logEdit) : self
    {
        $this->logEdit = $logEdit;

        return $this;
    }

    public function isLoggingEdit() : bool
    {
        return $this->logEdit;
    }

    public function setLogHistory(bool $logHistory) : self
    {
        $this->logHistory = $logHistory;

        return $this;
    }

    public function isLoggingHistory() : bool
    {
        return $this->logHistory;
    }

    public function setOldMessage(?string $oldMessage): self
    {
        $this->oldMessage = $oldMessage;

        return $this;
    }

    public function getOldMessage(): ?string
    {
        return $this->oldMessage;
    }

    protected function setupEditHistory(string $oldMessage): void
    {
        $replyBanner = $this->getReplyBanner();

        $replyBanner->banner_edit_count++;

        $options = $this->options();
        if ($options->editLogDisplay['enabled'] && $this->isLoggingEdit())
        {
            $replyBanner->banner_last_edit_user_id = \XF::visitor()->user_id;
            $replyBanner->banner_last_edit_date = \XF::$time;
        }

        if ($options->editHistory['enabled'] && $this->isLoggingHistory())
        {
            $this->setOldMessage($oldMessage);
        }
    }

    public function setRawText(string $rawText) : self
    {
        $replyBanner = $this->getReplyBanner();
        $setupHistory = !$replyBanner->isChanged('message');
        $oldRawText = $replyBanner->raw_text;

        $replyBanner->raw_text = $rawText;

        if ($setupHistory && $replyBanner->isChanged('raw_text') && $oldRawText !== null)
        {
            $this->setupEditHistory($oldRawText);
        }

        return $this;
    }

    public function setIsActive(bool $active) : self
    {
        $replyBanner = $this->getReplyBanner();

        if (strlen($replyBanner->raw_text ?? '') === 0)
        {
            $active = false;
        }

        $replyBanner->banner_state = $active;

        return $this;
    }

    public function setUser(UserEntity $user) : self
    {
        $this->getReplyBanner()->banner_user_id = $user->user_id;

        return $this;
    }

    protected function finalSetup(): void
    {
    }

    protected function _validate() : array
    {
        $this->finalSetup();

        $replyBanner = $this->getReplyBanner();
        $replyBanner->preSave();

        return $replyBanner->getErrors();
    }

    protected function _save() : AbstractBannerEntity
    {
        $db = $this->db();
        $db->beginTransaction();

        $replyBanner = $this->getReplyBanner();
        $replyBanner->save(true, false);

        $content = $this->getContent();
        $content->updateHasSvContentBanner($replyBanner->banner_state, false);

        $oldMessage = $this->getOldMessage();
        if ($oldMessage)
        {
            $this->getEditHistoryRepo()->insertEditHistory(
                $replyBanner->getEntityContentType(),
                $replyBanner->getEntityId(),
                \XF::visitor(),
                $oldMessage,
                $this->request()->getIp()
            );
        }

        $db->commit();

        return $replyBanner;
    }

    public function delete(): void
    {
        $db = $this->db();
        $db->beginTransaction();

        $replyBanner = $this->getReplyBanner();
        $replyBanner->delete(true, false);

        $content = $this->getContent();
        $content->updateHasSvContentBanner(false, false);

        $db->commit();
    }

    protected function app() : BaseApp
    {
        return $this->app;
    }

    protected function request() : HttpRequest
    {
        return $this->app()->request();
    }

    protected function options() : \ArrayObject
    {
        return $this->app()->options();
    }

    /**
     * @return EditHistoryRepo|Repository
     */
    protected function getEditHistoryRepo() : EditHistoryRepo
    {
        return $this->repository('XF:EditHistory');
    }
}