<?php

date_default_timezone_set('America/Sao_Paulo');
require __DIR__ . '/vendor/autoload.php';

/**
 * Created by PhpStorm.
 * User: sacdigital
 * Date: 20/06/17
 * Time: 16:45
 */

class IdealProcces{

    /**
     * Contrastes da classe
     */



    /**
     * Variaveis para o processo
     */
    var $process_name = null;
    var $is_locked = false;


    /**
     * Variaveis para manipulacao de arquivos
     */

    var $file_name = null;
    var $type_return = null;
    var $return_file = null;


    const TYPE = [
        'HTML','JSON','ARRAY'
    ];


    function __construct($process_name=null)
    {


        $this->dir_process_lock = __DIR__ . '/proccess_lock/';
        $this->dir_kill = __DIR__ . '/kill/';

        if($process_name!=null){
            $this->process_name = $process_name;
        }

        if(!is_dir($this->dir_process_lock)){
            mkdir($this->dir_process_lock,0777);
        }
        if(!is_dir($this->dir_kill)){
            mkdir($this->dir_kill,0777);
        }


        $this->parans();

    }


    function paramToJson($s){
        echo $s;

        $s = str_replace([
            ','
        ],'","',$s);

        $s = str_replace([
            'sep'
        ],'","',$s);

        $s = str_replace([
            '{'
        ],'{"',$s);
        $s = str_replace([
            ':'
        ],'":"',$s);
        $s = str_replace([
            '}'
        ],'"}',$s);

        return $s;
    }


    function check_kill(){

        $args = $_SERVER['argv'];

        foreach ($args as $arg) {
            $arg = str_replace('proccess=','',$arg);

            if(strpos($arg,':')){
                $arg = explode(':',$arg);

                $_arg[$arg[0]] = $arg[1];

            }

        }

        $_arg = (Object) $_arg;
        if(!is_object($_arg) and !is_array($args)) return;



        if(!isset($_arg->proccess_name)) return;
        if(file_exists($this->dir_kill.$_arg->proccess_name)){
            unlink($this->dir_kill.$_arg->proccess_name);
            exit;
        }


    }


    function parans($args=null){


        if($args==null ){
            $args = $_SERVER['argv'];
        }

        if(!isset($args) or count($args) <= 0) return;

        foreach ($args as $arg){
            $_arg = explode('proccess=',$arg);
            if(count($_arg) > 1){
                $_arg = json_decode($_arg[1]);

                if(!is_object($_arg)) break;

                if(isset($_arg->action)){
                    $this->actions($_arg);
                }


            }
        }

    }


    function actions($data){


        switch ($data->action){
            case 'kill':

                if(isset($data->proccess)){
                    $this->kill($data->proccess);
                }

                break;
        }
    }

    function kill($proccess){

        $file = $this->dir_process_lock.$proccess.'.json';
        if(!file_exists($file)){
         file_put_contents($file,json_encode(["kill"=>true]));
        }

        $data = file_get_contents($file);
        $data = json_decode($data);

        $data->kill = true;
        file_put_contents($file,json_encode($data));


    }



    /**
     * setar o nome para o processo
     */
    function setName($name){
        $this->process_name = $name;
    }


    /**
     * @param string $process_name
     * @param string $file
     */

    function getParans($data){
        return "proccess='{proccess_name:$data->proccess_name}'";

    }


    function run($process_name="proccess",$file= "proccess.php"){

        $_process_name = $process_name;

        $this->process_name = $_process_name;
        $this->proccess_name = $_process_name;

        $process_name = $process_name;
        $locked = 1;
        $file_proccess = $this->dir_process_lock . $process_name . '.json';
        while ($locked) {

            if (! file_exists($file_proccess)) {


                file_put_contents($file_proccess,json_encode([
                    'time'=>time(),
                    'file_proccess'=>$file,
                    'proccess_name'=>$_process_name
                ]));

                $locked = 0;

                break;

            } else {

                $data = json_decode(file_get_contents($file_proccess));

                if(isset($data) and !isset($data->proccess_name) or $data == null){
                    file_put_contents($file_proccess,json_encode([
                        'time'=>time(),
                        'file_proccess'=>$file,
                        'proccess_name'=>$_process_name,
                        'kill'=>1
                    ]));
                }else{

                    if($data->proccess_name != $file){

                        file_put_contents($file_proccess,json_encode([
                            'time'=>time(),
                            'file_proccess'=>$file,
                            'proccess_name'=>$_process_name,
                            'kill'=>1
                        ]));

                        sleep(1);

                        if(file_exists($file_proccess)){
                            unlink($file_proccess);
                        }


                    }else{

                    $locked = 1;

                    break;
                    }


                }

            }
        }

        if($locked==1){
            $this->is_locked = $locked;
            $ftime = filemtime($file_proccess);
            $this->process_date = date('Y-m-d H:i:s',$ftime);


            if($this->check_process($data->proccess_name,$data->file_proccess .' '. $this->getParans($data)) != true){
                $this->is_locked = false;
            }



        }


        if($this->is_locked == false){
            $this->start();
        }




    }



    function check_process($p1,$command='php') {

        $command = str_replace("'",'',$command);
        exec("ps aux | grep -i 'php ".$command."'", $pids);

        if(count($pids) > 0){



            print_r($pids);

            $grep = false;
            $i= 0;
            foreach ($pids as $list){

                $_pids = explode(' ',$list);

                if(!in_array('grep',$_pids) == true){


                    foreach ($_pids as $id){
                        if(!in_array('grep',$pids) == true){
                            if(is_numeric($id)){

                                return $id;
                            }
                        }

                    }



                }

            }







        }

    }


    /**
     * Iniciar o processo caso o arquivo {lock/nome_do_processo}.lock não exista na pasta
     */

    function start(){

        $loop = React\EventLoop\Factory::create();

        $loop->addPeriodicTimer(1, function($timer) use($loop) {

            global $process;


            $glob = glob(__DIR__.'/proccess_lock/'.$this->proccess_name.'.json');
            $data = null;
            foreach ($glob as $g){

                if(!isset($process[$g])){

                    $data = json_decode(file_get_contents($g));

                    if($this->check_process($data->file_proccess) != true){
                        print_r($data);

                    }$process[$g] = $data;

                }

            }
            if(isset($data) and count($data) > 0){

                foreach ($process as $data){

                    if($this->check_process($data->file_proccess . ' '.$this->getParans($data)) == false and file_exists($this->dir_process_lock.$data->proccess_name.'.json')){

                        $_process[$data->proccess_name] = new React\ChildProcess\Process('php '.$data->file_proccess . ' '.$this->getParans($data));

                        $_process[$data->proccess_name]->on('exit', function($exitCode, $termSignal) use($data,$loop) {

                            $loop->stop();

                        });


                        $loop->addTimer(0.01, function($timer) use ($_process,$data,$i,$loop) {
                            $_process[$data->proccess_name]->start($timer->getLoop());


                            if(file_exists($this->dir_process_lock.$data->proccess_name.'.json')){

                                $get_data = file_get_contents($this->dir_process_lock.$data->proccess_name.'.json');
                                $get_data = json_decode($get_data);
                                $get_data->pid = $_process[$data->proccess_name]->getPid();

                                file_put_contents($this->dir_process_lock.$data->proccess_name.'.json',json_encode($get_data));



                                $_process[$data->proccess_name]->stdout->on('data', function($output,$loop) {

                                    echo "Child script says: {$output}";


                                });
                            }

                        });

                        $loop->addPeriodicTimer(0.1*5, function($timer) use($data,$loop) {

                            echo PHP_EOL;
                            echo time();


                            if(file_exists($this->dir_process_lock.$data->proccess_name.'.json')) {
                                $get_data = file_get_contents($this->dir_process_lock . $data->proccess_name . '.json');
                                $get_data = json_decode($get_data);

                                if (isset($get_data->kill)) {
                                    unlink($this->dir_process_lock . $data->proccess_name . '.json');

                                    if(isset($get_data->pid)){
                                        exec('kill -9 ' . $get_data->pid);
                                    }

                                    exit();
                                }

                            }else{

                                if(isset($data->pid)){
                                    exec('kill -9 ' . $data->pid);
                                }

                                exit();
                            }

                        });


                    }


                }


            }


        });

        $loop->run();


        return;

    }


    /**
     * Iniciar processo para checar se o processo realmente esta rodado caso o mesmo estaja lockado
     */
    function check(){

    }



    /**
     * Criar arquivos junto com usar pastas para garantir que as mesmas sempre existão
     */
    function setFile($file_name,$data,$type_return=0){

        $this->file_name = $file_name;
        $this->type_return = $type_return;

        $this->folder($file_name);

        if(is_array($data) or is_object($data)):
            $data = json_encode($data);
        endif;

        file_put_contents($file_name,$data);

        $this->getFile();
    }

    /**
     * Criar pastas para garantir que as mesmas sempre existão
     */
    function folder($dirpath, $mode=0777) {
        $dir_name = dirname($dirpath);
        return is_dir($dir_name) || mkdir($dir_name, $mode, true);
    }


    function getFile($file_name=null,$type_return=0){

        if($file_name == null):
            $this->file_name = $file_name;
        endif;

        if($type_return == null):
            $this->type_return = $type_return;
        endif;

        if($this->return_file == null) return;

        $this->return_file = file_get_contents($file_name);

        if(self::TYPE[$type_return] == 'JSON'):
            $this->return_file = json_decode($this->return_file);
        elseif
        (self::TYPE[$type_return] == 'ARRAY'):
            $this->return_file = json_decode($this->return_file,true);
        elseif
        (self::TYPE[$type_return] == 'HTML'):
            $this->return_file = json_decode($this->return_file,true);
        endif;


        return $this->return_file;
    }
}