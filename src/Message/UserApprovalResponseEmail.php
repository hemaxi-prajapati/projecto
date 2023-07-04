<?php

namespace App\Message;


use App\Entity\ProjectAssignment;

class UserApprovalResponseEmail
{
    private $projectAssignment;

    public function __construct(ProjectAssignment $projectAssignment)
    {
        $this->projectAssignment = $projectAssignment;

    }

    public function getProjectAssignment(): projectAssignment
    {
        return $this->projectAssignment;
    }

}