<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class b2c_local_staff{
    public function __construct(&$app){
        $this->app = $app;
        kernel::single('base_session')->start();
    }
    
    /**
     * 判断当前员工是否登录
     */
    public function is_login(){
        $staff = $this->get_staff_session();
        return $staff ? true : false;
    }
    
    /**
     * 获取当前员工ID
     */
    public function get_staff_id(){
        return $staff_id = $this->get_staff_session();
    }
    
     /**
     * 设置员工登录session staff_id local_id
     */
    public function set_staff_session($staff_id,$local_id){
        unset($_SESSION['ome']);
        $_SESSION['ome']['staff'] = $staff_id;
        $_SESSION['ome']['local'] = $local_id;
    }
    
    /**
     * 获取员工登录session staff_id local_id
     */
    public function get_staff_session(){
        if(isset($_SESSION['ome']['staff']) &&  $_SESSION['ome']['local']){
            return $_SESSION['ome'];
        }else{
            return false;
        }
    }
}

