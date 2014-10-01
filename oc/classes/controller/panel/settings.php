<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Controller SETTINGS contains all basic configurations displayed to Admin.
 */


class Controller_Panel_Settings extends Auth_Controller {

    public function __construct($request, $response)
    {
        parent::__construct($request, $response);
        
        Breadcrumbs::add(Breadcrumb::factory()->set_title(__('Settings'))->set_url(Route::url('oc-panel',array('controller'  => 'settings'))));

    }

    public function action_index()
    {
        HTTP::redirect(Route::url('oc-panel',array('controller'  => 'settings','action'=>'general')));  
    }

    /**
     * Contains all data releated to new advertisment optional form inputs,
     * captcha, uploading text file  
     * @return [view] Renders view with form inputs
     */
	public function action_product()
    {
        Breadcrumbs::add(Breadcrumb::factory()->set_title(__('product')));
        $this->template->title = __('product');
       
        // all form config values
        $product = new Model_Config();
        $config = $product->where('group_name', '=', 'product')->find_all();

        // save only changed values
        if($this->request->post())
        {
            foreach ($config as $ci) 
            {   
                
                $allowed_formats = '';
                $config_res = $this->request->post($ci->config_key);
                if($config_res != $ci->config_value)
                {
                    if($ci->config_key == 'formats')
                    {
                      foreach ($config_res as $key => $value) 
                      {
                          $allowed_formats .= $value.",";
                      }
                      $config_res = $allowed_formats;
                    } 
                    
                    $ci->config_value = $config_res;
                    try {

                        $ci->save();

                    } catch (Exception $e) {
                        echo $e;
                    }
                }
            }
            $this->redirect(Route::url('oc-panel',array('controller'=>'settings','action'=>'product')));
            
        }

        $this->template->content = View::factory('oc-panel/pages/settings/product', array('config'=>$config));
    }


    /**
     * Email configuration 
     * @return [view] Renders view with form inputs
     */
    public function action_email()
    {
        Breadcrumbs::add(Breadcrumb::factory()->set_title(__('Email')));
        $this->template->title = __('Email');

        // all form config values
        $emailconf = new Model_Config();
        $config = $emailconf->where('group_name', '=', 'email')->find_all();

        // save only changed values
        if($this->request->post())
        {
        	foreach ($config as $c) 
            {
                $config_res = $this->request->post($c->config_key); 

                if($config_res != $c->config_value)
                {
                    $c->config_value = $config_res;
                    try {
                        $c->save();
                    } catch (Exception $e) {
                        echo $e;
                    }
                }
            }
            // Cache::instance()->delete_all();
            Alert::set(Alert::SUCCESS, __('Email Configuration updated'));
            $this->redirect(Route::url('oc-panel',array('controller'=>'settings','action'=>'email')));
        }

        $this->template->content = View::factory('oc-panel/pages/settings/email', array('config'=>$config));
    }

    /**
     * All general configuration related with configuring site.
     * @return [view] Renders view with form inputs
     */
    public function action_general()
    {
        // validation active 
        //$this->template->scripts['footer'][]= '/js/oc-panel/settings.js';
        
        Breadcrumbs::add(Breadcrumb::factory()->set_title(__('General')));
        $this->template->title = __('General');

        // all form config values
        $generalconfig = new Model_Config();
        $config = $generalconfig->where('group_name', '=', 'general')->find_all();
        $config_img = $generalconfig->where('group_name', '=', 'image')->find_all();
        $config_i18n = $generalconfig->where('group_name', '=', 'i18n')->find_all();
        
        foreach ($config as $c) 
        {
            $forms[$c->config_key] = array('key'=>$c->config_key, 'value'=>$c->config_value);
        }
        
        foreach ($config_img as $c)
        {
            $forms_img[$c->config_key] = array('key'=>$c->config_key, 'value'=>$c->config_value);
        }
        // config i18n configs
        foreach ($config_i18n as $c)
        {
            $i18n[$c->config_key] = array('key'=>$c->config_key, 'value'=>$c->config_value);
        }
        
        //not updatable fields
        $do_nothing = array('menu','locale','allow_query_language','charset');

        // save only changed values
        if($this->request->post())
        {
            //save general
        	foreach ($config as $c) 
            {   
                $config_res = $this->request->post($c->config_key);
                if($config_res != $c->config_value AND !in_array($c->config_key, $do_nothing))
                {
                    $c->config_value = $config_res;
                    try {
                        $c->save();
                    } catch (Exception $e) {
                        echo $e;
                    }
                }
                  
            }
            //save image config
            foreach ($config_img as $ci) 
            {   
                
                $allowed_formats = '';
                $config_res = $this->request->post($ci->config_key);
                if($config_res != $ci->config_value)
                {
                    if($ci->config_key == 'allowed_formats')
                    {
                        
                      foreach ($config_res as $key => $value) 
                      {
                          $allowed_formats .= $value.",";
                      }
                      $config_res = $allowed_formats;
                    }
                    
                    if($ci->config_key == 'aws_s3_domain')
                    {
                        switch ($config_res)
                        {
                            case 'bn-s3':
                                $s3_domain = $this->request->post('aws_s3_bucket').'.s3.amazonaws.com';
                                break;
                                
                            case 'bn':
                                $s3_domain = $this->request->post('aws_s3_bucket');
                                break;
                                
                            default:
                                $s3_domain = 's3.amazonaws.com/'.$this->request->post('aws_s3_bucket');
                                break;
                        }
                        $config_res = $s3_domain.'/';
                    }
                    
                    $ci->config_value = $config_res;
                    try {

                        $ci->save();

                    } catch (Exception $e) {
                        echo $e;
                    }
                }
            }
            //save i18n
            foreach ($config_i18n as $cn) 
            {   
                $config_res = $this->request->post($cn->config_key);

                if($config_res != $cn->config_value AND !in_array($cn->config_key, $do_nothing))
                {
                    $cn->config_value = $config_res;
                    try {
                        $cn->save();
                    } catch (Exception $e) {
                        echo $e;
                    }
                }
            }

            
            Alert::set(Alert::SUCCESS, __('General Configuration updated'));
            $this->redirect(Route::url('oc-panel',array('controller'=>'settings','action'=>'general')));
        }

        $pages = array(''=>__('Deactivated'));
        foreach (Model_Content::get_pages() as $key => $value) 
            $pages[$value->seotitle] = $value->title;

        $this->template->content = View::factory('oc-panel/pages/settings/general', array('pages'=>$pages, 'forms'=>$forms, 'forms_img'=>$forms_img,'i18n'=>$i18n));
    }

    /**
     * Payment deatails and paypal configuration can be configured here
     * @return [view] Renders view with form inputs
     */
    public function action_payment()
    {
        // validation active 
        //$this->template->scripts['footer'][]= '/js/oc-panel/settings.js';
        
        Breadcrumbs::add(Breadcrumb::factory()->set_title(__('Payments')));
        $this->template->title = __('Payments');

        // all form config values
        $paymentconf = new Model_Config();
        $config = $paymentconf->where('group_name', '=', 'payment')->find_all();
        
        $paypal_currency = Paypal::get_currency(); // currencies limited by paypal


        // save only changed values
        if($this->request->post())
        {
        	foreach ($config as $c) 
            {
                $config_res = $this->request->post($c->config_key); 

                
                if($c->config_key == 'paypal_currency')
                {   
                    $config_res = $paypal_currency[core::post('paypal_currency')];
                }

                if($config_res != $c->config_value)
                {
                    $c->config_value = $config_res;
                    try {
                        $c->save();
                    } catch (Exception $e) {
                        echo $e;
                    }
                }
            }
            
            Alert::set(Alert::SUCCESS, __('Payment Configuration updated'));
            $this->redirect(Route::url('oc-panel',array('controller'=>'settings','action'=>'payment')));
        }

        $pages = array(''=>__('Deactivated'));
        foreach (Model_Content::get_pages() as $key => $value) 
            $pages[$value->seotitle] = $value->title;

        $this->template->content = View::factory('oc-panel/pages/settings/payment', array('config'          => $config,
                                                                                           'pages'          => $pages,
                                                                                          'paypal_currency' => $paypal_currency));
    }


    /**
     * affiliate configuration can be configured here
     * @return [view] Renders view with form inputs
     */
    public function action_affiliates()
    {

        
        Breadcrumbs::add(Breadcrumb::factory()->set_title(__('Affiliates')));
        $this->template->title = __('Affiliates');

        // all form config values
        $paymentconf = new Model_Config();
        $config = $paymentconf->where('group_name', '=', 'affiliate')->find_all();
        


        // save only changed values
        if($this->request->post())
        {
            foreach ($config as $c) 
            {
                $config_res = $this->request->post($c->config_key); 

                if($config_res != $c->config_value)
                {
                    $c->config_value = $config_res;
                    try {
                        $c->save();
                    } catch (Exception $e) {
                        echo $e;
                    }
                }
            }
            
            Alert::set(Alert::SUCCESS, __('Affiliate Configuration updated'));
            $this->redirect(Route::url('oc-panel',array('controller'=>'settings','action'=>'affiliates')));
        }

        $pages = array(''=>__('Deactivated'));
        foreach (Model_Content::get_pages() as $key => $value) 
            $pages[$value->seotitle] = $value->title;

        $this->template->content = View::factory('oc-panel/pages/settings/affiliates', array('config'          => $config,
                                                                                           'pages'          => $pages));
    }


}//end of controller