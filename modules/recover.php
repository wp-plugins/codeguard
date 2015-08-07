<?php
class recover extends module_as3b {

    private $files = array();

    public function run()
    {
        include_once(main::getPluginDir() . '/libs/beacon.php');
        codeguard_beacon('recover', $this->params);
        try {
            main::log(lang::get('Starting restore process', false));
            if (isset($this->params['name']) && isset($this->params['type'])) {
                if ($this->params['type'] == 'local') {
                    $this->local();
                } elseif ($this->params['type'] == 's3') {
                    $this->s3();
                }
                main::log(lang::get('Finished restore process', false));
            } else {
                $this->setError( lang::get('Error: Could not find the specified backup.', false) );
            }
        } catch (Exception $e) {
            include_once(main::getPluginDir() . '/libs/beacon.php');
            codeguard_beacon('recovererror', $this->params);
            $this->setError($e->message);
        }

    }


    private function s3()
    {
        $amazon_option = get_option(PREFIX_CODEGUARD . 'setting');
        if ($amazon_option) {
            require_once main::getPluginDir() . '/libs/classes/aws-autoloader.php';
            try {
                $dir = BACKUP_DIR . '/' . $this->params['name'] ;
                $credentials = new Aws\Common\Credentials\Credentials($amazon_option['access_key_id'], $amazon_option['secret_access_key']);
                $client = Aws\S3\S3Client::factory(array( 'credentials' => $credentials ) );
                main::log( lang::get( "Downloading backup", false) );
                $keys = $client->listObjects(array('Bucket' => $amazon_option['bucket'], 'Prefix' => $amazon_option['prefix'] . '/' . $this->params['name'] ))->getIterator();//->getPath('Contents/*/Key');
                if (isset($keys['Contents'])) {
                    $n = count($keys['Contents']);
                    main::mkdir($dir);
                    main::log( lang::get( "Downloading files", false) );
                    for($i = 0; $i < $n; $i++) {
                        $path = explode("/", $keys['Contents'][$i]['Key']);
                        if(isset($path[1]) && isset($path[2]) && !empty($path[2])) {
                            $part_path = substr($keys['Contents'][$i]['Key'], strlen($amazon_option['prefix'] . '/'));
                            main::log(str_replace("%s", $part_path,  lang::get( "Downloading part: %s", false)) );
                            $result = $client->getObject(array(
                            'Bucket' => $amazon_option['bucket'],
                            'Key'    => $keys['Contents'][$i]['Key'],
                            'SaveAs' => BACKUP_DIR . '/' . $part_path
                            ));
                        }
                    }
                    main::log( lang::get( "Finished downloading files", false ) );


                    $this->local();
                    if (is_dir($dir)) {
                        main::remove($dir);
                    }
                } else {
                    $this->setError(lang::get("Error: Could not download backup.", false));
                }

            } catch (Exception $e) {
                include_once(main::getPluginDir() . '/libs/beacon.php');
                codeguard_beacon('recovererror', $this->params);
                $this->setError($e->getMessage());
            } catch(S3Exception $e) {
                include_once(main::getPluginDir() . '/libs/beacon.php');
                codeguard_beacon('recovererror', $this->params);
                $this->setError($e->getMessage());
            }
        } else {
            $this->setError( lang::get( 'Error: Could not connect to CodeGuard. Please update your connection settings.', false) );
        }
    }

    private function local()
    {
        $this->files = readDirectrory(BACKUP_DIR . '/' . $this->params['name'], array('.zip'));
        include main::getPluginDir() . '/libs/pclzip.lib.php';

        if ( ($n = count($this->files)) > 0) {
            for($i = 0; $i< $n; $i ++) {
                main::log( str_replace('%s', basename($this->files[$i]), lang::get("Extracting part: %s", false)) );
                $this->archive = new PclZip($this->files[$i]);
                $file_in_zip = $this->archive->extract(PCLZIP_OPT_PATH, ABSPATH, PCLZIP_OPT_REPLACE_NEWER);
            }
            if (file_exists(BACKUP_DIR . '/' . $this->params['name'] . '/mysqldump.sql')) {
                main::log( lang::get( "Starting database restore", false ) );
                $mysql = $this->incMysql();
                $mysql->restore(BACKUP_DIR . '/' . $this->params['name'] . '/mysqldump.sql');
                main::remove(BACKUP_DIR . '/' . $this->params['name'] . '/mysqldump.sql');
                main::log( lang::get( "Finished database restore", false ) );
            }
        }
    }


}
