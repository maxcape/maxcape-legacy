<?php
    class userfunctions extends dbfunctions {
        // Generate a unique user_token to be used to check login status
        function generateUserToken() {
            return uniqid('', false);
        }

        // Attempt to validate a user based on their user_token and user_name cookies
        function validateUser($token = "", $username = "") {
            $db = $this->connectToDatabase($this->database);

            if($token == "") {
                $token = mysql_real_escape_string($_COOKIE['user_token']);
            }

            if($username == "") {
                $username = mysql_real_escape_string($_COOKIE['user_name']);
            }

            if($db['found']) {
                $userdata = $this->queryToAssoc("SELECT UserID, Username, Email, RSN, JoinDate, ProfileViews FROM users WHERE Username='$username' AND LoginKey='$token' LIMIT 1");
                if(count($userdata) > 0) {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['userid'] = $userdata['UserID'];
                    $_SESSION['username'] = $userdata['Username'];
                    $_SESSION['rsn'] = $userdata['RSN'];

                    return true;
                } else {
                    return false;
                }
            } else {
                $_SESSION['loggedin'] = false;

                return false;
            }
        }

        // Login a user based on their username and password inputs
        function login($username, $password) {
            $db = $this->connectToDatabase($this->database);

            if($db['found']) {
                $username = mysql_real_escape_string($username);
                $password = mysql_real_escape_string($password);

                $info = $this->queryToAssoc("SELECT * FROM users WHERE Username='$username' LIMIT 1");

                if(count($info) > 0) {
                    if(crypt($password, $info['Password']) == $info['Password']) {
                        $token = $this->generateUserToken();
                        $userid = $info['UserID'];
                        $realusername = $info['Username'];

                        $this->query("UPDATE users SET LoginKey='$token' WHERE UserID='$userid'");

                        $this->validateUser($token, $realusername);

                        setcookie("user_name", $realusername, time() + (86400 * 14), "/");
                        setcookie("user_token", $token, time() + (86400 * 14), "/");

                        $this->disconnectFromDatabase($db['handle']);
                        return 0;
                    } else {
                        $this->disconnectFromDatabase($db['handle']);
                        return 1;
                    }
                } else {
                    $this->disconnectFromDatabase($db['handle']);
                    return 2;
                }
            } else {
                $this->disconnectFromDatabase($db['handle']);
                return 3;
            }
        }

        // Quick check to see if the user is logged in
        // If user is not logged in BUT has a token cookie, attempt to validate
        function isLoggedIn() {
            if(isset($_SESSION['loggedin'])) {
                return $_SESSION['loggedin'];
            } else {
                if(isset($_COOKIE['user_token']) && isset($_COOKIE['user_name'])) {
                    return $this->validateUser();
                } else {
                    return false;
                }
            }
        }

        // Unset the loggedin session and expire the user token cookie.
        function logout() {
            if(isset($_SESSION['loggedin'])) {
                unset($_SESSION['loggedin']);
                $db = $this->connectToDatabase($this->database);

                if($db['found']) {
                    $userid = $_SESSION['userid'];
                    $this->query("UPDATE users SET LoginKey=NULL WHERE UserID='$userid'");
                    $this->disconnectFromDatabase($db['handle']);

                    session_destroy();
                } else {
                    $this->disconnectFromDatabase($db['handle']);
                }


                //force expire a cookie by setting it to expire in the past.
                setcookie("user_token", "", time() - (86400 * 14), "/");
            }
        }
    }
