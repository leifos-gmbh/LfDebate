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

namespace Leifos\Debate\RTE;

class RTEHelper
{
    public function __construct()
    {

    }

    public function getTextInputFromPost(int $nr) : string
    {
        return \ilUtil::stripSlashes(
            $_POST["form_input_" . $nr],
            true,
            "<strong><em><br><p><ol><li><ul>"
        );
    }

    public function initRTE() : void
    {
        $rte = new \ilTinyMCE();
        //$rte->setInitialWidth($this->getInitialRteWidth());

        $rte->removeAllPlugins();

        // #13603 - "paste from word" is essential
        /*
        $rte->addPlugin("paste");
        //Add plugins 'lists', 'code' and 'link': in tinymce 3 it wasnt necessary to configure these plugins
        $rte->addPlugin("lists");
        $rte->addPlugin("link");
        $rte->addPlugin("code");*/

        $rte->addPlugin("lists");

        if (method_exists($rte, 'removeAllContextMenuItems')) {
            $rte->removeAllContextMenuItems(); //https://github.com/ILIAS-eLearning/ILIAS/pull/3088#issuecomment-805830050
        }

        // #11980 - p-tag is mandatory but we do not want the icons it comes with
        $rte->disableButtons(array("underline", "anchor", "alignleft", "aligncenter",
                                   "alignright", "alignjustify", "formatselect", "removeformat",
                                   "cut", "copy", "paste", "pastetext")); // JF, 2013-12-09
        $rte->addCustomRTESupport(
            0,
            "",
            ["strong", "em", "u", "ol", "li", "ul", "p", "span", "br"]
        );
    }

}