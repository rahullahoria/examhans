<?php
/**
 * Created by PhpStorm.
 * User: spider-ninja
 * Date: 2/20/17
 * Time: 5:04 PM
 */

function getUserStatus($userMd5){
    $request = \Slim\Slim::getInstance()->request();

    $user = json_decode($request->getBody());


    /*
     * [
     *      {
     *          subject_name:'Reasoning',
     *          topics: [
     *                      {
     *                          id: 4
     *                          name:'asdfa',
     *                          questions:'45',
     *                          amount:'200',
     *                          status: done
     *                          },
     *                          {
     *                          id: 5
     *                          name:'sadfdfa',
     *                          questions:40,
     *                          amount:0,
     *                          status: not-done
     *                          }
     *                  ]
     *      },
     * 'Quantitative Aptitude','English','General Awareness','Computer Knowledge']
     *
     * */
    $sql = "SELECT distinct d.id as subject_id, d.name as subject_name, a.exam_id
            FROM `users` as a
            inner join topic_exam_mappings as b
            inner join topics as c
            inner join subjects as d
            WHERE a.md5 = :user_md5
            and a.exam_id = b.exam_id
            and b.topic_id = c.id
            and c.subject_id = d.id ";

    $sqlGetTopics = "SELECT b.id as topic_id, b.name as topic_name, c.no_of_question, d.amount_made
                            FROM topic_exam_mappings as a
                            inner join topics as b
                            inner join patterns as c
                            left join tests as d on d.topic_id = b.id
                            where
                            a.exam_id = :exam_id
                            and a.topic_id = b.id
                            and b.subject_id = :subject_id
                            and c.topic_id = b.id";

    $response = array();

    try {
        $db = getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindParam("user_md5", $userMd5);



        $stmt->execute();
        $subjects = $stmt->fetchAll(PDO::FETCH_OBJ);
        //var_dump($subjects);die();

        foreach($subjects as $subject){

            $stmt = $db->prepare($sqlGetTopics);

            $stmt->bindParam("exam_id", $subject->exam_id);
            $stmt->bindParam("subject_id", $subject->subject_id);

            $stmt->execute();
            $topics = $stmt->fetchAll(PDO::FETCH_OBJ);
            //var_dump($topics);die();

            $response[] = array(
                            'subject_name' => $subject->subject_name,
                            'subject_id' => $subject->subject_id,
                            "topics" => $topics
                        );
            $topics = null;

        }

        //var_dump($response);die();

        $db = null;

        echo '{"subjectssaf": ' . json_encode($subjects) . '}';



    } catch (Exception $e) {
        //error_log($e->getMessage(), 3, '/var/tmp/php.log');
        echo '{"error":{"text":"' . $e->getMessage() . '"}}';
    }
}