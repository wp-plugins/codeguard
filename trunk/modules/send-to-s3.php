<?php

if (!class_exists('send_to_s3')) {
    class send_to_s3 extends module_as3b {

        public function run()
        {
            include main::getPluginDir() . '/libs/classes/aws-autoloader.php';
            include_once(main::getPluginDir() . '/libs/beacon.php');
            codeguard_beacon('send',  $this->params);
            $ad = $this->params['access_details'];
            main::log( lang::get('Starting upload to CodeGuard', false) );
            $files = $this->params['files'];
            $dir = (isset($ad['dir'])) ? $ad['dir'] : '/';

            $credentials = new Aws\Common\Credentials\Credentials($ad['AccessKeyId'], $ad['SecretAccessKey']);
            $client = Aws\S3\S3Client::factory(array( 'credentials' => $credentials ) );
            try {
                $n = count($files);
                for($i=0; $i < $n; $i++) {
                    $filePath = preg_replace('#[/\\\\]+#', '/', BACKUP_DIR . '/' . $dir . '/' . $files[$i]);
                    $key = ($dir) ? $dir .'/'. basename($filePath) : basename($filePath);
                    $key = ltrim( preg_replace('#[/\\\\]+#', '/', $key), '/' );//if first will be '/', file not will be uploaded, but result will be ok
                    $putRes = $client->putObject(array("Bucket" => $ad['bucket'], 'Key' => $ad['prefix'] . '/' . $key, 'Body' => fopen($filePath, 'r+')));
                    main::log( str_replace('%s', basename($filePath) , lang::get("Uploading part: %s", false ) ) ) ;
                    if ( !isset($putRes['RequestId']) || empty($putRes['RequestId'])) {
                        main::log( str_replace('%s', basename($filePath) , lang::get("Failed to upload part: %s", false ) ) ) ;
                    }
                }
                main::log( lang::get('Finished uploading backup', false) );
            } catch (Exception $e) {
                include_once(main::getPluginDir() . '/libs/beacon.php');
                codeguard_beacon('sendfail',  $this->params);
                main::log('Error: ' . $e->getMessage());
                $this->setError($e->getMessage());
                return false;
            } catch(S3Exception $e) {
                include_once(main::getPluginDir() . '/libs/beacon.php');
                codeguard_beacon('sendfail',  $this->params);
                main::log('Error: ' . $e->getMessage());
                $this->setError($e->getMessage());
                return false;
            }
            return true;

        }

    }
}
