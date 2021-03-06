<?php

/*
 * This file is part of Hiject.
 *
 * Copyright (C) 2016 Hiject Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hiject\Controller;

use Hiject\Core\ObjectStorage\ObjectStorageException;
use Hiject\Core\Thumbnail;

/**
 * Avatar File Controller.
 */
class AvatarFileController extends BaseController
{
    /**
     * Display avatar page.
     */
    public function show()
    {
        $user = $this->getUser();

        $this->response->html($this->helper->layout->user('avatar_file/show', [
            'user' => $user,
        ]));
    }

    /**
     * Upload Avatar.
     */
    public function upload()
    {
        $user = $this->getUser();

        if (!$this->avatarFileModel->uploadImageFile($user['id'], $this->request->getFileInfo('avatar'))) {
            $this->flash->failure(t('Unable to upload the file.'));
        }

        $this->response->redirect($this->helper->url->to('AvatarFileController', 'show', ['user_id' => $user['id']]));
    }

    /**
     * Remove Avatar image.
     */
    public function remove()
    {
        $this->checkCSRFParam();
        $user = $this->getUser();
        $this->avatarFileModel->remove($user['id']);
        $this->userSession->refresh($user['id']);
        $this->response->redirect($this->helper->url->to('AvatarFileController', 'show', ['user_id' => $user['id']]));
    }

    /**
     * Show Avatar image (public).
     */
    public function image()
    {
        $user_id = $this->request->getIntegerParam('user_id');
        $size = $this->request->getStringParam('size', 48);
        $filename = $this->avatarFileModel->getFilename($user_id);
        $etag = md5($filename.$size);

        $this->response->withCache(365 * 86400, $etag);
        $this->response->withContentType('image/jpeg');

        if ($this->request->getHeader('If-None-Match') !== '"'.$etag.'"') {
            $this->response->send();
            $this->render($filename, $size);
        } else {
            $this->response->status(304);
        }
    }

    /**
     * Render thumbnail from object storage.
     *
     * @param string $filename
     * @param int    $size
     */
    private function render($filename, $size)
    {
        try {
            $blob = $this->objectStorage->get($filename);

            Thumbnail::createFromString($blob)
                ->resize($size, $size)
                ->toOutput();
        } catch (ObjectStorageException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
