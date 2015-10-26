<?php

namespace anek77713\yii2\oauth2server\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use anek77713\yii2\oauth2server\filters\ErrorToExceptionFilter;
use anek77713\yii2\oauth2server\models\OauthClients;
use dektrium\user\models\User;

class DefaultController extends \yii\rest\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className()
            ],
        ]);
    }
    
    public function actionToken()
    {
        $server = $this->module->getServer();
        $request = $this->module->getRequest();

        $oauthClientModel = new OauthClients();
        $client = $oauthClientModel::find()->where(['client_id' => $request->request("client_id")])->one();

        $userModel = new User();
        $user = $userModel::find()->where(['email' => $request->request("username")])->one();

        if ($client->client_id && $user->id) {
            if ($user->id !== $client->user_id) {
                throw new \yii\web\HttpException(400, 'Invalid user and client credentials combination', 0);
            }
        }

        $response = $server->handleTokenRequest($request);
        return $response->getParameters();
    }
}
