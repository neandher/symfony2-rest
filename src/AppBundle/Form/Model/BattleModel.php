<?php

namespace AppBundle\Form\Model;

use AppBundle\Entity\Programmer;
use AppBundle\Entity\Project;

class BattleModel
{
    private $project;
    private $programmer;

    /**
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param mixed $project
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
    }

    /**
     * @return mixed
     */
    public function getProgrammer()
    {
        return $this->programmer;
    }

    /**
     * @param mixed $programmer
     */
    public function setProgrammer(Programmer $programmer)
    {
        $this->programmer = $programmer;
    }
}