<?php

namespace app\controllers;

use app\models\entities\Evaluations;
use app\models\entities\IdeaUsers;
use app\models\entities\Thematics;
use app\models\entities\Type;
use app\models\entities\Users;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
		if(!Yii::$app->user->isGuest)
		{
			return $this->redirect(['main']);
		}

		$model=new LoginForm();
		if($model->load(Yii::$app->request->post())&&$model->login())
		{
			return $this->redirect(['main']);
		}

		$model->password='';
		return $this->render('login',[
			'model'=>$model,
		]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionMain()
    {
		$ideaUsers_dataProvider=new ActiveDataProvider([
			'query'=>IdeaUsers::find(),
		]);
		$chart=[];

		foreach(Thematics::find()->select(['id_thematic','thematic_name'])->all() as $value)
		{
			/** @noinspection PhpPossiblePolymorphicInvocationInspection */
			$count=IdeaUsers::find()->select('thematic_id')->where([
				'thematic_id'=>$value->id_thematic
			])->count();

			if($count==0)
			{
				/** @noinspection PhpPossiblePolymorphicInvocationInspection */
				$chart[$value->thematic_name]=0;
			}
			else
			{
				/** @noinspection PhpPossiblePolymorphicInvocationInspection */
				$chart[$value->thematic_name]=100/(IdeaUsers::find()->select('id')->count()/$count);
			}
		}

		$users_dataProvider=new ActiveDataProvider([
			'query'=>Users::find(),
		]);

		$randomIdeaUsers_dataProvider=new ActiveDataProvider([
			'query'=>IdeaUsers::find()->orderBy('RAND()')->limit(3),
		]);

        return $this->render('main',[
			'ideaUsers_dataProvider'=>$ideaUsers_dataProvider,
			'chart'=>$chart,
			'thematics'=>Thematics::find()->all(),
			'users_dataProvider'=>$users_dataProvider,
			'randomIdeaUsers_dataProvider'=>$randomIdeaUsers_dataProvider
		]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionEvaluation($idea_users, $type)
    {
		$evaluation=new Evaluations();
		$evaluation->id_user=Yii::$app->user->id;
		$evaluation->id_idea_users=$idea_users;
		$evaluation->id_type=$type;
		$evaluation->save();

		return $this->redirect(['index']);
    }

	public function actionLk()
	{
		return $this->render('lk');
		// $model = new ContactForm();
		// if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
		//     Yii::$app->session->setFlash('contactFormSubmitted');

		//     return $this->refresh();
		// }
		// return $this->render('contact', [
		//     'model' => $model,
		// ]);
	}

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionStore()
    {
        return $this->render('store');
    }

    public function actionGame()
    {
        return $this->render('game');
    }

    public function actionMyIdea()
    {
        return $this->render('my-idea');
    }

    public function actionAddIdea()
    {
        return $this->render('add-idea');
    }

    public function actionFavoriteIdea()
    {
        return $this->render('favorite-idea');
    }

    public function actionIdeaDel()
    {
        return $this->render('idea-del');
    }

    public function actionModerIdeaClose()
    {
        return $this->render('moder-idea-close');
    }

    public function actionHistoryStore()
    {
        return $this->render('history-store');
    }

    public function actionBuyProduct()
    {
        return $this->render('buy-product');
    }

    public function actionIdeaMore()
    {
        return $this->render('idea-more');
    }

    public function actionCreate()
    {
        $model = new IdeaUsers();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id_idea_user]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
}
