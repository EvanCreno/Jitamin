<?php

/*
 * This file is part of Hiject.
 *
 * Copyright (C) 2016 Hiject Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../Base.php';

use Hiject\Action\TaskAssignCategoryColor;
use Hiject\Bus\Event\TaskEvent;
use Hiject\Model\CategoryModel;
use Hiject\Model\ProjectModel;
use Hiject\Model\TaskFinderModel;
use Hiject\Model\TaskModel;

class TaskAssignCategoryColorTest extends Base
{
    public function testChangeCategory()
    {
        $categoryModel = new CategoryModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);
        $taskFinderModel = new TaskFinderModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test']));
        $this->assertEquals(1, $categoryModel->create(['name' => 'c1', 'project_id' => 1]));

        $event = new TaskEvent([
            'task_id' => 1,
            'task'    => [
                'project_id' => 1,
                'color_id'   => 'red',
            ],
        ]);

        $action = new TaskAssignCategoryColor($this->container);
        $action->setProjectId(1);
        $action->setParam('color_id', 'red');
        $action->setParam('category_id', 1);

        $this->assertTrue($action->execute($event, TaskModel::EVENT_CREATE_UPDATE));

        $task = $taskFinderModel->getById(1);
        $this->assertNotEmpty($task);
        $this->assertEquals(1, $task['category_id']);
    }

    public function testWithWrongColor()
    {
        $categoryModel = new CategoryModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $taskModel = new TaskModel($this->container);

        $this->assertEquals(1, $projectModel->create(['name' => 'test1']));
        $this->assertEquals(1, $taskModel->create(['project_id' => 1, 'title' => 'test']));
        $this->assertEquals(1, $categoryModel->create(['name' => 'c1', 'project_id' => 1]));

        $event = new TaskEvent([
            'task_id' => 1,
            'task'    => [
                'project_id' => 1,
                'color_id'   => 'blue',
            ],
        ]);

        $action = new TaskAssignCategoryColor($this->container);
        $action->setProjectId(1);
        $action->setParam('color_id', 'red');
        $action->setParam('category_id', 1);

        $this->assertFalse($action->execute($event, TaskModel::EVENT_CREATE_UPDATE));
    }
}
