<?php

/**
 * Klass som lagrar information om varje enskild spelare i tävlingen.
 *
 * @author samuel
 */
class Player {

    public $id;
    public $firstname;
    public $lastname;
    public $gender;
    public $wins;
    public $rest;
    public $history;
    public $boardNumber;
    public $played_games;
    public $todays_wins;
    public $rested_last;
    public $winratio;
    public $same_sex;
	public $pair_history;
    public function __construct($id_, $firstname_, $lastname_, $gender_,
            $wins_ = 0, $played_games_ = 0,$todays_wins_ = 0, $boardNumber_ = NULL, $rest_ = 0, 
            $history_ = array(), $rested_last_ = 0, $winratio = 0, $same_sex_=0, $pair_history_ = array()) {
        $this->id = $id_;
        $this->firstname = $firstname_;
        $this->lastname = $lastname_;
        $this->gender = $gender_;
        $this->wins = $wins_;
        $this->rest = $rest_;
        $this->history = $history_;
        $this->boardNumber = $boardNumber_;
        $this->played_games = $played_games_;
        $this->todays_wins = $todays_wins_;
         $this->rested_last = $rested_last_;
         $this->same_sex = $same_sex_;
		 $this->pair_history = $pair_history_;
    }

    public function getGender() {
        return $this->gender;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getFirstname() {
        return $this->firstname;
    }

    public function setFirstname($firstname) {
        $this->firstname = $firstname;
    }

    public function getLastname() {
        return $this->lastname;
    }

    public function setLastname($lastname) {
        $this->lastname = $lastname;
    }

    public function getWins() {
        return $this->wins;
    }

    public function setWins($wins) {
        $this->wins = $wins;
    }

    public function getRest() {
        return $this->rest;
    }

    public function setRest() {
        $this->rest++;
    }

}

?>
