<?php

namespace App\EventListener;

use App\Repository\TaskWithProjectRepository;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use App\Entity\TaskWithProject;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Query\Expr\Func;

class TaskWithProjectSubscriber implements EventSubscriber
{

    public function __construct(private TaskWithProjectRepository $taskWithProjectRepository)
    {
        $this->taskWithProjectRepository = $taskWithProjectRepository;
    }
    public function getSubscribedEvents()
    {
        return ['preUpdate'];
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {

        if ($args->getObject() instanceof TaskWithProject) {
            $changeset = $args->getEntityChangeSet();

            // $taskNew = $args->getObject();
            // $taskOld = ($this->taskWithProjectRepository->find($taskNew->getID()));
        }
    }
}

// dd($taskOld);