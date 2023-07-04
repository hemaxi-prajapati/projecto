<?php

namespace App\Message;

final class ReportPdfGeneration
{
    /*
     * Add whatever properties and methods you need
     * to hold the data for this message class.
     */

    // ($tasks,$project,$UserReletedToProject,$allProject,$IsReport,$auther) )
    // public function __construct(private $tasks, private $project, private $UserReletedToProject, private $allProject, private $IsReport, private $auther)
    public function __construct(private $projectId,private $User)
    {
    }

       public function getProjectId(): string
       {
           return $this->projectId;
       }
       public function getUser()
       {
           return $this->User;
       }
}
