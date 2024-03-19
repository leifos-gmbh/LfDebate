<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace Leifos\Debate;

use ILIAS\DI\Container;
use ILIAS\DI\HTTPServices;
use ILIAS\UI\Component\Symbol\Avatar\Avatar;

class GUIFactory
{
    /**
     * @var Container
     */
    protected $DIC;
    /**
     * @var DataFactory
     */
    protected $data_factory;
    /**
     * @var DomainFactory
     */
    protected $domain_factory;
    /**
     * @var HTTPServices
     */
    protected $http;

    public function __construct(
        Container $DIC,
        DataFactory $data_factory,
        DomainFactory $domain_factory
    ) {
        $this->DIC = $DIC;
        $this->http = $DIC->http();
        $this->data_factory = $data_factory;
        $this->domain_factory = $domain_factory;
    }

    public function export(int $obj_id): \Leifos\Debate\Export\ExportGUI
    {
        return new \Leifos\Debate\Export\ExportGUI($this->domain_factory, $this, $obj_id);
    }

    public function posting(
        \ilLfDebatePlugin $plugin,
        string $type,
        Avatar $avatar,
        string $name,
        string $create_date,
        string $last_edit,
        string $title,
        string $text,
        string $title_link = "",
        bool $show_pin = false
    ): PostingUI {
        return new PostingUI(
            $plugin,
            $type,
            $avatar,
            $name,
            $create_date,
            $last_edit,
            $title,
            $text,
            $title_link,
            $show_pin
        );
    }

    public function comment(
        \ilLfDebatePlugin $plugin,
        string $type,
        Avatar $avatar,
        string $name,
        string $create_date,
        string $last_edit,
        string $title,
        string $text
    ): CommentUI {
        return new CommentUI(
            $plugin,
            $type,
            $avatar,
            $name,
            $create_date,
            $last_edit,
            $title,
            $text
        );
    }

    public function postingLight(
        \ilLfDebatePlugin $plugin,
        string $type,
        string $create_date,
        string $title,
        string $text
    ): PostingLightUI {
        return new PostingLightUI(
            $plugin,
            $type,
            $create_date,
            $title,
            $text
        );
    }

    public function commentLight(
        \ilLfDebatePlugin $plugin,
        string $type,
        string $create_date,
        string $title,
        string $text
    ): CommentLightUI {
        return new CommentLightUI(
            $plugin,
            $type,
            $create_date,
            $title,
            $text
        );
    }

    public function profileReminder(\ilLfDebatePlugin $plugin) : \DebateProfileReminderGUI {
        return new \DebateProfileReminderGUI(
            $this->domain_factory,
            $this,
            $plugin
        );
    }

    public function request() : GUIRequest
    {
        return new GUIRequest(
            $this->http,
            $this->domain_factory->refinery()
        );
    }

    public function ui() : \ILIAS\DI\UIServices
    {
        return $this->DIC->ui();
    }

    public function ctrl() : \ilCtrl
    {
        return $this->DIC->ctrl();
    }
}
