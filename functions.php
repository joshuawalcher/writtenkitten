<?php
require_once('config.php');
/* 
@description checks for existence of database. Called by fillOutHeader and Save
@return boolean
*/
function checkForDatabase()
{
    //check config
    if(isset($dbUser) && $dbUser != '' && isset($dbPass) && $dbPass != '') {
        $conn = connectToDB();
        return true;
    }
    return false;
}

/* 
@description returns the things to put in the header, which may be nothing
@return string
*/
function fillOutHeader($storage)
{
    if (checkForDatabase()) {
        if(isLoggedIn($storage)) {
            $username = getUsername($storage);
            return '
            Logged in as ' . $username . '
            <ul id="menu">
                <li><a href="">Log Out</a></li>
                <li><a href="">Manage User</a></li>
            </ul>';
        } else {
            return '
            <ul id="menu">
                <li><a href="">Sign In</a></li>
                <li><a href="">Register</a></li>
            </ul>';
        }
    }
}

/* 
@description save the work
@return boolean
*/
function saveWork($user, $work)
{
    // eventually this will check and save all tabs also. So instead of a single block of text, it will be an array of blocks of text.
    // check for database connection
    // connect to database
    // see if this work has ever been saved before
        // if so, update    
        // if not, insert       
}

function login($username, $password)
{
    $data = $_POST;
    if (isset($data['login_user']) && isset($data['login_password'])) {
        $email = cleanString(strtolower($data['login_user']));
        $password = cleanString($data['login_password']);
        $conn = connectToDB();
        $pull = mysqli_query($conn,"SELECT userid FROM user WHERE username = '$username' AND `password` = PASSWORD('$password')");
        $row = mysqli_fetch_array($pull,MYSQLI_ASSOC);

        if (isset($row['userid']) && (int)$row['userid'] > 0) {
            //we have a winner. make 'em logged in.
            $userId = (int)$row['userid'];
            //generate a new token
            $token = createToken();
            $ip = $_SERVER['REMOTE_ADDR'];
            // insert session into table after removing all previous sessions
            // ToDo: eventually we should just mark it inactive rather than full-on delete.
            // that way we can have a history of logins and say stuff like "last logged in on mm/dd/YYYY from Peru"
            mysqli_query($conn,"DELETE FROM user_sessions WHERE userid = '$userId'");
            $init = mysqli_query($conn,"INSERT INTO user_sessions (token,ipv4,userid) VALUES ('$token','$ip','$userId')");
            mysqli_close($conn);
            //create cookie that lasts for a week (or until they delete their cookies)
            setcookie('usertoken',$token,time() + (7*24*60*60));
            return 'success';
        } else {
            //username or password didn't match.
            //ToDo: we need to track these and punish brute force attempts.
            //>logError($data);
            return'error: We could not find an account with that username and password. Please try again.';
        }

    } else {
        return 'Something went wrong. Please try logging in again.';
    }
    return 'error: Something went wrong. Please try logging in again.';
}

function logout($userid)
{
    $token = cleanString($token);
    //delete session row from table
    $conn = connectToDB();
    $kill = mysqli_query($conn,"DELETE FROM user_sessions WHERE token = '$token'");
    //delete cookie
    setcookie('usercookie','',time()-1000);
    return 'success';
}

/* 
@description connect to database using settings in config file (if present)
@return MySQLi connection object
*/
function connectToDB()
{
    $namea = $dbName;
	$usera = $dbUser;
	$passworda = $dbPass;
	$conn = mysqli_connect($dbHost,$usera,$passworda,$namea) or die(mysqli_error());
	return $conn;
}

function createToken()
{
    //cryptographically secure
    return bin2hex(openssl_random_pseudo_bytes(16));
}

function createUser($data)
{

}

function checkForExistingSession($token)
{
    $token = cleanString($token);
    //connect
    $conn = connectToDB();
    //check for token in database
    $pull = mysqli_query($conn,"SELECT count(*) as count from user_sessions WHERE token = '$token'");

    $row = mysqli_fetch_array($pull,MYSQLI_ASSOC);
    if((int)$row['count'] > 0){
        //it's a good token
        $pull = mysqli_query($conn,"SELECT created_at,ipv4 from user_sessions WHERE token = '$token'");
        $row = mysqli_fetch_array($pull,MYSQLI_ASSOC);
        mysqli_close($conn);

        //is the token less than 7 days old?

        if((int)(time() - strtotime($row['created_at'])) <= (7*24*60*60)){
            //they have a valid login.
                return true;
        } else {
            //timestamp is old.
            return false;
        }
    } else {
        //token isn't in DB.
        return false;
    }
    return false;
}

function checkUsernameForExisting($username)
{
    $token = cleanString($username);
    $sql = "SELECT username from user
    WHERE username = '$username'";
    $conn = connectToDB();
    $pull = mysqli_query($conn,$sql);
    $row = mysqli_fetch_array($pull,MYSQLI_ASSOC);
    if(isset($row['username'])){
        return true;
    } else {
        return false;
    }
}

function checkEmailForExisting($email)
{
    $token = cleanString($username);
    if(str_replace(' ','',$email) != '') {
        $sql = "SELECT email from user
        WHERE email = '$email'";
        $conn = connectToDB();
        $pull = mysqli_query($conn,$sql);
        $row = mysqli_fetch_array($pull,MYSQLI_ASSOC);
        if(isset($row['username'])){
            return true;
        } else {
            return false;
        }
    } else {
        //someone sent in a blank email or just spaces
        return false;
    }
}

function getEmailFromToken($token)
{
    $token = cleanString($token);
    $sql = "SELECT u.username from user u
    LEFT JOIN user_sessions s ON s.userid = u.userid
    WHERE s.token = '$token'";
    $conn = connectToDB();
    $pull = mysqli_query($conn,$sql);
    $row = mysqli_fetch_array($pull,MYSQLI_ASSOC);
    if(isset($row['username'])){
        return $row['username'];
    } else {
        return false;
    }
}

function getUserFromToken($token)
{
    $token = cleanString($token);
    $sql = "SELECT u.userid, u.username, u.email from user u
    LEFT JOIN user_sessions s ON s.userid = u.userid
    WHERE s.token = '$token'";

    $conn = connectToDB();
    $pull = mysqli_query($conn,$sql);
    $row = mysqli_fetch_array($pull,MYSQLI_ASSOC);

    if(isset($row['firstname'])){
        $row['granteename'] = str_replace('"','',$row['granteename']);
        $row['granteename'] = str_replace("\x92",'',$row['granteename']);
        return json_encode($row);
    } else {
        return false;
    }
}

/**
** returns array
**/
function getTokenFromUserId($userId)
{
    $userId = (int)$userId;
    if($userId > 0){
        $sql = "SELECT token from user_sessions WHERE userid = '$userId'";
        $conn = connectToDB();
        $pull = mysqli_query($conn,$sql);
        $rows = mysqli_fetch_array($pull,MYSQLI_ASSOC);
        return $rows;
    } else {
        //that wasn't a user ID. Return false;
        return 'error: that user is not logged in';
    }
}

function getSessionFromToken($token)
{
    $token = cleanString($token);
    $sql = "SELECT * from user_sessions WHERE token = '$token'";
    $conn = connectToDB();
    $pull = mysqli_query($conn,$sql);
    $rows = mysqli_fetch_array($pull,MYSQLI_ASSOC);
    return $rows;
}

function getUserIdFromToken($token)
{
    $token = cleanString($token);
    $sql = "SELECT userid from user_sessions WHERE token = '$token'";
    $conn = connectToDB();
    $pull = mysqli_query($conn,$sql);
    $row = mysqli_fetch_array($pull,MYSQLI_ASSOC);
    $userId = (int)$row['userid'];
    return $userId;
}

function getSessionByUserId($userId)
{
    $userId = (int)$userId;
    if($userId > 0){
        $sql = "SELECT * from user_sessions WHERE userId = '$userId'";
        $conn = connectToDB();
        $pull = mysqli_query($conn,$sql);
        $rows = mysqli_fetch_array($pull,MYSQLI_ASSOC);
        return $rows;
    } else {
        return false;
    }
}

function killOldSessions()
{
    $sql = "DELETE FROM user_sessions WHERE created_at <= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $conn = connectToDB();
    mysqli_query($conn,$sql);
    mysqli_close($conn);
    return true;
}

function tryReset($email)
{
  	$conn = connectToDB();
  	$email = strtolower($email);
    // remove bad characters
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    // validate e-mail
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      //check to see if that email exists.
    	$pull = mysqli_query(
				$conn,
				"SELECT COUNT(*) as count FROM user WHERE username = '$email'"
    	);
    	$row = mysqli_fetch_array($pull, MYSQLI_ASSOC);

    	if ($row['count'] > 0) {
            //it does, so we need to create a token and insert it into the database
            $resetToken = uniqid();
            $resetDate = date('Y-m-d', time());

    		$insert = mysqli_query(
					$conn,
					"INSERT INTO user_reset_sessions (username, token, reset_date)
					VALUES ('$email', '$resetToken', '$resetDate')"
    		);
	        //now add that token to the end of a URL and send the user a notification email with a link to reset their password.
	        $url = 'https://writtenkitten.co/forgot.php?token=' . $token;
	        $to = $email;

            $message = '
                Hello,
                <br>
                <br>
                We have received a request to reset your Written? Kitten! password. If that was you, please click the link below to choose a new one:
                <br>
                <br>' .
                '<a href="' . $url . '">Choose a new password</a>' .
                '<br>
                <br>
                If you did not request this password change, please ignore this email and your password will not change.<br><br>
                Thank you!<br><br>Written? Kitten! Admin
            ';
            $subject = 'Password Reset Request';
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: Written? Kitten! <noreply@writtenkitten.co>' . "\r\n";
            $headers .= 'Reply-To: noreply@writtenkitten.co';
            mail($to, $subject, $message, $headers);
            return 'An email has been sent to the address provided for resetting your password.';
        } else {
    		return 'error: Something went wrong. Please try again.';
    	}
    } else {
		return 'error: The submitted value was not a valid email address.';
    }
}

function doReset($email, $password, $token)
{
	$conn = connectToDB();
	$email = strtolower($email);
    // remove bad characters
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    // validate e-mail
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
	    //resetting a password. validate token and email against the token in the database.
	    $sql = "
	    	SELECT COUNT(*) as count
	    	FROM password_reset_sessions
	    	WHERE token = '$token'
	    	AND username = '$email'
	    ";
	  	$pull = mysqli_query($conn, $sql);
	  	$row = mysqli_fetch_array($pull, MYSQLI_ASSOC);

	  	if ($row['count'] > 0) {
		    //redirect them to login with a message saying that their password has been reset.
		    $userPull = mysqli_query(
		    	$conn,
		    	"SELECT userid FROM user WHERE username = '$email'"
		    );
		    $userRow = mysqli_fetch_array($userPull, MYSQLI_ASSOC);
		    $userId = $userRow['userid'];
		    $updateSql = "
					UPDATE `user`
					SET `password` = PASSWORD('$password')
					WHERE userid = '$userId'
		    ";
		    $updatePassword = mysqli_query($conn, $updateSql);

		    if ($updatePassword) {
		    	mysqli_query(
						$conn,
						"DELETE FROM password_reset_sessions WHERE token = '$token'"
		    	);
			    return 'Your password has been reset. You can log in here.';
		    } else {
                return 'error: The password was not updated. Please try again.';
		    }
	  	} else {
	  		return 'error: Something went wrong. Please try again.';
	  	}
    } else {
	  	return 'error: That is not a valid email address.';
    }
}

/*
@description: clean up a string
*/
function cleanString($thing)
{
	return filter_var(trim($thing),FILTER_SANITIZE_STRING);
}
/*
@description: alias for cleanString function
*/
function stringClean($string)
{
	return cleanString($string);
}