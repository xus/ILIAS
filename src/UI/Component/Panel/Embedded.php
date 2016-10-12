<?php

namespace ILIAS\UI\Component\Panel;

/**
 * This describes a Embedded Panel.
 */
interface Embedded extends Panel {
    /**
     * Sets the card to be displayed on the right of the Embedded Panel
     * @param \ILIAS\UI\Component\Card\Card $card
     * @return Embedded
     */
    public function withCard(\ILIAS\UI\Component\Card\Card $card);

    /**
     * Gets the card to be displayed on the right of the Embedded Panel
     * @return \ILIAS\UI\Component\Card\Card | null
     */
    public function getCard();
}
