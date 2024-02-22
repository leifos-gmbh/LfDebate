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

namespace Leifos\Debate\Export;

use ILIAS\UI\Component\Button\Button;
use Leifos\Debate\DomainFactory;
use ILIAS\UI\Component\Deck\Deck;
use ILIAS\UI\Component\Panel\Listing\Listing;

class ExportGUI
{
    /**
     * @var int
     */
    protected $obj_id;
    /**
     * @var DomainFactory
     */
    protected $domain;
    /**
     * @var \Leifos\Debate\GUIFactory
     */
    protected $gui;

    public function __construct(
        DomainFactory $domain,
        \Leifos\Debate\GUIFactory $gui,
        int $obj_id
    ) {
        $this->gui = $gui;
        $this->domain = $domain;
        $this->obj_id = $obj_id;
    }

    public function addExportButton(\ilToolbarGUI $toolbar) : void
    {
        $f = $this->gui->ui()->factory();
        $pl = $this->domain->plugin();

        $modal = $f->modal()->roundtrip(
            $pl->txt("export"),
            $this->getListing()
        );

        $b = $f->button()->standard(
            $pl->txt("export"),
            "#"
        )->withOnClick($modal->getShowSignal());

        $toolbar->addComponent($b);
        $toolbar->addComponent($modal);
    }

    protected function getListing()
    {
        $f = $this->gui->ui()->factory();
        $ctrl = $this->gui->ctrl();
        $pl = $this->domain->plugin();

        $pm = $this->domain->posting($this->obj_id);

        $cards = [];
        foreach ($pm->getContributors() as $c) {
            $ctrl->setParameterByClass(\ilObjLfDebateGUI::class, "contrib", (string) $c["id"]);
            $button = $f->button()->standard($pl->txt("export"),
                $ctrl->getLinkTargetByClass(\ilObjLfDebateGUI::class, "exportContributor")
            );
            $ctrl->setParameterByClass(\ilObjLfDebateGUI::class, "contrib", "");
            $image = $f->image()->standard(
                \ilObjUser::_getPersonalPicturePath($c["id"], "xsmall"),
                $c["lastname"] . ", " . $c["firstname"]
            );
            $cards[] = $f->card()->standard(
                $c["lastname"] . ", " . $c["firstname"],
                $image
            )->withSections([$button]);
        }
        return $f->deck($cards)->withNormalCardsSize();
    }
}
