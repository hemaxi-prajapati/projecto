<?php

namespace App\Message;


use App\Entity\TaskWithProject;
use App\Entity\User;


class CreateTaskEmail
{
    private $taskWithProject;
    private $user;


    public function __construct(TaskWithProject $taskWithProject, User $user)
    {
        $this->taskWithProject = $taskWithProject;
        $this->user = $user;

    }

    public function getTaskWithProject(): TaskWithProject
    {
        return $this->taskWithProject;
    }


    public function getUser(): user
    {
        return $this->user;
    }
}