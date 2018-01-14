<?php

namespace Eccube\Log;

use Monolog\Processor\UidProcessor;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class EccubeLogCustomProcessor
 *
 * @package Eccube\Log\Monolog\Helper
 */
class EccubeLogProcessor
{

    private $session;
    private $sessionId;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }


    /**
     * log.ymlの内容に応じたHandlerの設定を行う
     *
     * @param array $record
     * @return array
     */
    public function processRecord(array $record)
    {
        // Processorの内容をログ出力
        $uidProcessor = new UidProcessor(8);

        $record['level_name'] = sprintf("%-5s", $record['level_name']);

        if (!$this->sessionId) {
            $this->sessionId = substr($this->session->getId(), 0, 8) ?: '????????';
        }

        $record['session_id'] = $this->sessionId.'-'.substr(uniqid('', true), -8);;
        $record['user_id'] = null;

        $record['uid'] = $uidProcessor->getUid();

        // クラス名などを一旦保持し、不要な情報は削除
        $line = $record['extra']['line'];
        $functionName = $record['extra']['function'];
        // php5.3だとclass名が取得できないため、ファイル名を元に出力
        // $className = $record['extra']['class'];
        $className = $record['extra']['file'];

        // 不要な情報を削除
        unset($record['extra']['file']);
        unset($record['extra']['line']);
        unset($record['extra']['class']);
        unset($record['extra']['function']);

        $record['class'] = pathinfo($className, PATHINFO_FILENAME);
        $record['function'] = $functionName;
        $record['line'] = $line;

        return $record;
    }

}
