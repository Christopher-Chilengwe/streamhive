<?php

class User {

    private $con, $sqlData;

    public function __construct($con, $username) {
        $this->con = $con;

        $query = $this->con->prepare("SELECT * FROM users WHERE username = :un");
        $query->bindParam(":un", $username);
        $query->execute();

        // Fetch result
        $result = $query->fetch(PDO::FETCH_ASSOC);

        // Check if result is not false
        if ($result) {
            $this->sqlData = $result;
        } else {
            // Handle cases where the user does not exist
            $this->sqlData = [];
        }
    }

    public static function isLoggedIn() {
        return isset($_SESSION["userLoggedIn"]);
    }

    public function getUsername() {
        return isset($this->sqlData["username"]) ? $this->sqlData["username"] : null;
    }

    public function getName() {
        return (isset($this->sqlData["firstName"]) ? $this->sqlData["firstName"] : "") . " " . 
               (isset($this->sqlData["lastName"]) ? $this->sqlData["lastName"] : "");
    }

    public function getFirstName() {
        return isset($this->sqlData["firstName"]) ? $this->sqlData["firstName"] : null;
    }

    public function getLastName() {
        return isset($this->sqlData["lastName"]) ? $this->sqlData["lastName"] : null;
    }

    public function getEmail() {
        return isset($this->sqlData["email"]) ? $this->sqlData["email"] : null;
    }

    public function getProfilePic() {
        return isset($this->sqlData["profilePic"]) ? $this->sqlData["profilePic"] : null;
    }

    public function getSignUpDate() {
        return isset($this->sqlData["signUpDate"]) ? $this->sqlData["signUpDate"] : null;
    }

    public function getFullName() {
        $firstname = isset($this->sqlData["firstName"]) ? $this->sqlData["firstName"] : "";
        $lastname = isset($this->sqlData["lastName"]) ? $this->sqlData["lastName"] : "";

        return $firstname . " " . $lastname;
    }

    public function isSubscribedTo($userTo) {
        $query = $this->con->prepare("SELECT * FROM subscribers WHERE userTo=:userTo AND userFrom=:userFrom");
        $username = $this->getUsername();
        
        // Check if username is available
        if (!$username) return false;

        $query->bindParam(":userTo", $userTo);
        $query->bindParam(":userFrom", $username);
        $query->execute();

        return $query->rowCount() > 0;
    }

    public function getSubscriberCount() {
        $query = $this->con->prepare("SELECT * FROM subscribers WHERE userTo=:userTo");
        $username = $this->getUsername();

        // Check if username is available
        if (!$username) return 0;

        $query->bindParam(":userTo", $username);
        $query->execute();

        return $query->rowCount();
    }

    public function getSubscriptions() {
        $query = $this->con->prepare("SELECT userTo FROM subscribers WHERE userFrom=:userFrom");
        $username = $this->getUsername();

        // Check if username is available
        if (!$username) return [];

        $query->bindParam(":userFrom", $username);
        $query->execute();

        $subs = array();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($this->con, $row["userTo"]);
            array_push($subs, $user);
        }
        return $subs;
    }
}

?>
