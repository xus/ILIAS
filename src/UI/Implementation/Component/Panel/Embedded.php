<?php


namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Embedded
 * @package ILIAS\UI\Implementation\Component\Embedded
 */
class Embedded extends Panel implements C\Panel\Embedded {
    use ComponentHelper;

    /**
     * Card to be displayed on the right of the Sub Panel
     * @var C\Card\Card
     */
    private $card = null;

    /**
     * @inheritdoc
     */
    public function withCard(C\Card\Card $card){
        $clone = clone $this;
        $clone->card = $card;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getCard() {
        return $this->card;
    }

}
?>