<?php

/**
 * Klass som lagrar information om parningarna inför matchning.
 *
 * @author samuel
 */
class Pair {

    public $player1;
    public $player2;

    public function __construct($player1_, $player2_) {
        $this->player1 = $player1_;
        $this->player2 = $player2_;
    }

    public function getPlayer1() {
        return $this->player1;
    }

    public function getPlayer2() {
        return $this->player2;
    }

}

?>
