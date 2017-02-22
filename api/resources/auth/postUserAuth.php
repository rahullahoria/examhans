<?php
/**
 * Created by PhpStorm.
 * User: spider-ninja
 * Date: 6/22/16
 * Time: 2:13 PM
 */


function userAuth(){

    $request = \Slim\Slim::getInstance()->request();

    $user = json_decode($request->getBody());


    $sql = "SELECT a.`username`, a.`md5`, b.name
                FROM users as a
                inner join exams as b
                 WHERE a.exam_id = b.id
                 and a.username =:username
                 and a.password=:password ;";


    try {
        $db = getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindParam("username", $user->username);
        $stmt->bindParam("password", $user->password);

       // var_dump($user);die();

        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_OBJ);


        $db = null;

        if(count($users) == 1)
            echo '{"user": ' . json_encode($users[0]) . '}';
        else
            echo '{"auth": "false"}';


    } catch (PDOException $e) {
        //error_log($e->getMessage(), 3, '/var/tmp/php.log');
        echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
}


