<?php

namespace api\modules\v1\controllers;

use yii;
use api\controllers\ApiController;
use api\modules\v1\module;

/**
 * Default controller for the `v1` module
 */
class DefaultController extends ApiController
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $this->_error('0000');
    }
}
