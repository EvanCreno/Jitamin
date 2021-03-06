<?php

/*
 * This file is part of Hiject.
 *
 * Copyright (C) 2016 Hiject Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hiject\Api\Procedure;

use Hiject\Api\Authorization\ProjectAuthorization;
use Hiject\Api\Authorization\TaskAuthorization;
use Hiject\Filter\TaskProjectFilter;
use Hiject\Model\TaskModel;

/**
 * Task API controller.
 */
class TaskProcedure extends BaseProcedure
{
    public function searchTasks($project_id, $query)
    {
        ProjectAuthorization::getInstance($this->container)->check($this->getClassName(), 'searchTasks', $project_id);

        return $this->taskLexer->build($query)->withFilter(new TaskProjectFilter($project_id))->toArray();
    }

    public function getTask($task_id)
    {
        TaskAuthorization::getInstance($this->container)->check($this->getClassName(), 'getTask', $task_id);

        return $this->formatTask($this->taskFinderModel->getById($task_id));
    }

    public function getTaskByReference($project_id, $reference)
    {
        ProjectAuthorization::getInstance($this->container)->check($this->getClassName(), 'getTaskByReference', $project_id);

        return $this->formatTask($this->taskFinderModel->getByReference($project_id, $reference));
    }

    public function getAllTasks($project_id, $status_id = TaskModel::STATUS_OPEN)
    {
        ProjectAuthorization::getInstance($this->container)->check($this->getClassName(), 'getAllTasks', $project_id);

        return $this->formatTasks($this->taskFinderModel->getAll($project_id, $status_id));
    }

    public function getOverdueTasks()
    {
        return $this->taskFinderModel->getOverdueTasks();
    }

    public function getOverdueTasksByProject($project_id)
    {
        ProjectAuthorization::getInstance($this->container)->check($this->getClassName(), 'getOverdueTasksByProject', $project_id);

        return $this->taskFinderModel->getOverdueTasksByProject($project_id);
    }

    public function openTask($task_id)
    {
        TaskAuthorization::getInstance($this->container)->check($this->getClassName(), 'openTask', $task_id);

        return $this->taskStatusModel->open($task_id);
    }

    public function closeTask($task_id)
    {
        TaskAuthorization::getInstance($this->container)->check($this->getClassName(), 'closeTask', $task_id);

        return $this->taskStatusModel->close($task_id);
    }

    public function removeTask($task_id)
    {
        TaskAuthorization::getInstance($this->container)->check($this->getClassName(), 'removeTask', $task_id);

        return $this->taskModel->remove($task_id);
    }

    public function moveTaskPosition($project_id, $task_id, $column_id, $position, $swimlane_id = 0)
    {
        ProjectAuthorization::getInstance($this->container)->check($this->getClassName(), 'moveTaskPosition', $project_id);

        return $this->taskPositionModel->movePosition($project_id, $task_id, $column_id, $position, $swimlane_id);
    }

    public function moveTaskToProject($task_id, $project_id, $swimlane_id = null, $column_id = null, $category_id = null, $owner_id = null)
    {
        ProjectAuthorization::getInstance($this->container)->check($this->getClassName(), 'moveTaskToProject', $project_id);

        return $this->taskProjectMoveModel->moveToProject($task_id, $project_id, $swimlane_id, $column_id, $category_id, $owner_id);
    }

    public function duplicateTaskToProject($task_id, $project_id, $swimlane_id = null, $column_id = null, $category_id = null, $owner_id = null)
    {
        ProjectAuthorization::getInstance($this->container)->check($this->getClassName(), 'duplicateTaskToProject', $project_id);

        return $this->taskProjectDuplicationModel->duplicateToProject($task_id, $project_id, $swimlane_id, $column_id, $category_id, $owner_id);
    }

    public function createTask($title, $project_id, $color_id = '', $column_id = 0, $owner_id = 0, $creator_id = 0,
                                $date_due = '', $description = '', $category_id = 0, $score = 0, $swimlane_id = 0, $priority = 0,
                                $recurrence_status = 0, $recurrence_trigger = 0, $recurrence_factor = 0, $recurrence_timeframe = 0,
                                $recurrence_basedate = 0, $reference = '')
    {
        ProjectAuthorization::getInstance($this->container)->check($this->getClassName(), 'createTask', $project_id);

        if ($owner_id !== 0 && !$this->projectPermissionModel->isAssignable($project_id, $owner_id)) {
            return false;
        }

        if ($this->userSession->isLogged()) {
            $creator_id = $this->userSession->getId();
        }

        $values = [
            'title'                => $title,
            'project_id'           => $project_id,
            'color_id'             => $color_id,
            'column_id'            => $column_id,
            'owner_id'             => $owner_id,
            'creator_id'           => $creator_id,
            'date_due'             => $date_due,
            'description'          => $description,
            'category_id'          => $category_id,
            'score'                => $score,
            'swimlane_id'          => $swimlane_id,
            'recurrence_status'    => $recurrence_status,
            'recurrence_trigger'   => $recurrence_trigger,
            'recurrence_factor'    => $recurrence_factor,
            'recurrence_timeframe' => $recurrence_timeframe,
            'recurrence_basedate'  => $recurrence_basedate,
            'reference'            => $reference,
            'priority'             => $priority,
        ];

        list($valid) = $this->taskValidator->validateCreation($values);

        return $valid ? $this->taskModel->create($values) : false;
    }

    public function updateTask($id, $title = null, $color_id = null, $owner_id = null,
                                $date_due = null, $description = null, $category_id = null, $score = null, $priority = null,
                                $recurrence_status = null, $recurrence_trigger = null, $recurrence_factor = null,
                                $recurrence_timeframe = null, $recurrence_basedate = null, $reference = null)
    {
        TaskAuthorization::getInstance($this->container)->check($this->getClassName(), 'updateTask', $id);
        $project_id = $this->taskFinderModel->getProjectId($id);

        if ($project_id === 0) {
            return false;
        }

        if ($owner_id !== null && $owner_id != 0 && !$this->projectPermissionModel->isAssignable($project_id, $owner_id)) {
            return false;
        }

        $values = $this->filterValues([
            'id'                   => $id,
            'title'                => $title,
            'color_id'             => $color_id,
            'owner_id'             => $owner_id,
            'date_due'             => $date_due,
            'description'          => $description,
            'category_id'          => $category_id,
            'score'                => $score,
            'recurrence_status'    => $recurrence_status,
            'recurrence_trigger'   => $recurrence_trigger,
            'recurrence_factor'    => $recurrence_factor,
            'recurrence_timeframe' => $recurrence_timeframe,
            'recurrence_basedate'  => $recurrence_basedate,
            'reference'            => $reference,
            'priority'             => $priority,
        ]);

        list($valid) = $this->taskValidator->validateApiModification($values);

        return $valid && $this->taskModel->update($values);
    }
}
