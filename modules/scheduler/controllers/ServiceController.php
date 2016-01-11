<?php
/**
 * @copyright Copyright (c) 2012-2016 RNP
 * @license http://github.com/ufrgs-hyman/meican2#license
 */

namespace meican\scheduler\controllers;

use yii\console\Controller;
use Yii;

use meican\scheduler\components\CrontabManager;
use meican\scheduler\models\Task;

/**
 * @author Maurício Quatrin Guerreiro @mqgmaster
 */
class ServiceController extends Controller {
    
    public function beforeAction($action) {
        if (parent::beforeAction($action)) {
            return Yii::$app->id == "meican-console";
        } else {
            return false;
        }
    }

    private function getExecutionPath($taskId) {
        return Yii::$app->basePath."/yii init/services/crontab/task ".$taskId;
    }
    
    public function actionStart() {
        $crontab = new CrontabManager();
        $job = $crontab->newJob();
        $job->on('* * * * *');
        $job->doJob(Yii::$app->basePath."/yii init/services/crontab/listener");
        $crontab->add($job);
        $crontab->save(false);
        echo "MEICAN Scheduler Service Started\n";
    }
    
    public function actionStop() {
        $crontab = new CrontabManager();
        $job = $crontab->newJob();
        $job->on('* 1 * * *');
        $job->doJob("stopping service...");
        $crontab->add($job);
        $crontab->save(false);
        $this->delete($job->id);
        echo "MEICAN Scheduler Service Stopped\n";
    }
    
    public function actionListener() {
        $crons = Cron::find()
            ->where(['in' , 'status', [Cron::STATUS_PROCESSING, Cron::STATUS_DELETED]])
            ->orWhere(['external_id'=> null])->all();
        if ($crons) {
            foreach ($crons as $cron) {
                if ($cron->external_id != null) $this->delete($cron->external_id);
                
                switch ($cron->status) {
                    case Cron::STATUS_PROCESSING :
                        $this->create($cron);
                        break;
                    case Cron::STATUS_DELETED :
                        $cron->delete();
                }
            }
        }
    }
    
    private function create($cron) {
        $crontab = new CrontabManager();
        $job = $crontab->newJob();
        $job->on($cron->freq);
        $job->doJob($this->getExecutionPath($cron->id));
        $crontab->add($job);
        $crontab->save();
        $cron->external_id = $job->id;
        $cron->status = Cron::STATUS_ENABLED;
        $cron->save();
    }
    
    public function actionTask($id, $tag, $externalId) {
        $cron = Cron::findOne($id);
        if($cron) {
            if ($cron->status == Cron::STATUS_ENABLED)
                $cron->execute();
        } else {
            $this->delete($externalId);
        }
    }
    
    public function actionExecute() {
        $test = new Task;
        return $test->execute();
    }

    private function delete($externalId) {
        $crontab = new CrontabManager();
        $crontab->deleteJob($externalId);
        $crontab->save(false);
    }
}
