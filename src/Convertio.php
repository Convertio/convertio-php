<?php
/**
 * This file contains code about \Convertio\Convertio class
 */
namespace Convertio;

/**
 * Convertio API Wrapper
 */
class Convertio
{
    /**
     * Instance of API Class
     * @var API
     */
    private $api;

    /**
     * Internal Convertio conversion ID
     * @var string
     */
    private $convert_id;

    /**
     * Contain convertio current response data. More info: https://convertio.co/api/docs/
     * @var array
     */
    private $data;

    /**
     * Contain current step of the process (upload,wait,convert,finish,error). More info: https://convertio.co/api/docs/
     * @var string
     */
    public $step;

    /**
     * Contain current step progress in percents (0-100). More info: https://convertio.co/api/docs/
     * @var integer
     */

    public $step_percent;

    /**
     * Contain error message, if any. More info: https://convertio.co/api/docs/
     * @var array
     */
    public $error_message;

    /**
     * Contain public URI of result file. More info: https://convertio.co/api/docs/
     * @var string
     */
    public $result_public_url;

    /**
     * Contain size in bytes of result file. More info: https://convertio.co/api/docs/
     * @var integer
     */
    public $result_size;

    /**
     * Construct a new Convertio instance
     *
     * @param string $api_key API Key of your application. You can get your API Key on https://convertio.co/api/
     * @return \Convertio\Convertio
     *
     * @throws \Convertio\Exceptions\APIException if api key is missing or empty
     */
    public function __construct($api_key = null)
    {
        $this->api = new API($api_key);
        return $this;
    }

    /**
     * Wait for the conversion to finish
     *
     * @return \Convertio\Convertio
     *
     * @throws \Exception
     * @throws \Convertio\Exceptions\APIException if the Convertio API returns an error
     * @throws \Convertio\Exceptions\CURLException if there is a general HTTP / network error
     *
     */
    public function wait()
    {
        if ($this->step == 'finish' || $this->step == 'error') {
            return $this;
        }
        usleep(500000);
        return $this->status()->wait();
    }


    /**
     * Starts new conversion from local file
     *
     * @param string $input_fn path to local input file
     * @param string $output_format output format. You can view available formats on https://convertio.co/formats/
     * @return \Convertio\Convertio
     *
     * @throws \Exception
     * @throws \Convertio\Exceptions\APIException if the Convertio API returns an error
     * @throws \Convertio\Exceptions\CURLException if there is a general HTTP / network error
     *
     */
    public function start($input_fn, $output_format)
    {
        $data = array();
        $data['input'] = 'base64';
        $data['file'] = base64_encode(file_get_contents($input_fn));
        $data['filename'] = basename($input_fn);
        $data['outputformat'] = $output_format;

        $this->data = $this->api->post('/convert', $data);

        if ($this->data['status'] == 'error') {
            $this->step = 'error';
            $this->error_message = $this->data['error'];
        } else {
            $this->convert_id = $this->data['data']['id'];
            $this->step = 'convert';
        }

        return $this;
    }


    /**
     * Starts new conversion from remote url
     *
     * @param string $url URI of input file or web-page
     * @param string $out_format output format. You can view available formats on https://convertio.co/formats/
     * @return \Convertio\Convertio
     *
     * @throws \Exception
     * @throws \Convertio\Exceptions\APIException if the Convertio API returns an error
     * @throws \Convertio\Exceptions\CURLException if there is a general HTTP / network error
     *
     */
    public function startFromURL($url, $out_format)
    {
        $data = array();
        $data['input'] = 'url';
        $data['file'] = $url;
        $data['outputformat'] = $out_format;

        $this->data = $this->api->post('/convert', $data);

        if ($this->data['status'] == 'error') {
            $this->step = 'error';
            $this->error_message = $this->data['error'];
        } else {
            $this->convert_id = $this->data['data']['id'];
            $this->step = 'convert';
        }

        return $this;
    }

    /**
     * Download result file to local host
     *
     * @param string $local_fn path to local file to store the result
     * @return \Convertio\Convertio
     *
     * @throws \Exception
     * @throws \Convertio\Exceptions\APIException if the Convertio API returns an error
     * @throws \Convertio\Exceptions\CURLException if there is a general HTTP / network error
     *
     */
    public function download($local_fn)
    {
        $this->data = $this->api->get('/convert/'.$this->convert_id.'/dl/base64', false);
        $content = @base64_decode($this->data['data']['content']);

        if (empty($content)) {
            $this->step = 'error';
            $this->error_message = 'Empty result file';
            throw new Exceptions\APIException($this->error_message);
        }

        if (file_put_contents($local_fn, $content) === false) {
            $this->step = 'error';
            $this->error_message = 'Error saving local file';
            throw new Exceptions\APIException($this->error_message);
        }

        if ((!file_exists($local_fn)) || (filesize($local_fn) == 0)) {
            $this->step = 'error';
            $this->error_message = 'Error saving local file';
            throw new Exceptions\APIException($this->error_message);
        }

        return $this;
    }

    /**
     * Update status/progress of the conversion
     *
     * @return \Convertio\Convertio
     *
     * @throws \Exception
     * @throws \Convertio\Exceptions\APIException if the Convertio API returns an error
     * @throws \Convertio\Exceptions\CURLException if there is a general HTTP / network error
     *
     */
    public function status()
    {
        $data = $this->api->get('/convert/'.$this->convert_id.'/status', false);
        if ($data['status'] == 'error') {
            $this->step = 'error';
            $this->error_message = $data['error'];
        } else {
            $this->error_message = '';
            $this->step = $data['data']['step'];
            $this->step_percent = isset($data['data']['step_percent'])?$data['data']['step_percent']:0;

            if ($this->step == 'finish') {
                $this->result_public_url = $data['data']['output']['url'];
                $this->result_size = $data['data']['output']['size'];
            }
        }

        return $this;
    }

    /**
     * Delete current conversion and all associated files from Convertio hosts
     *
     * @return \Convertio\Convertio
     *
     * @throws \Exception
     * @throws \Convertio\Exceptions\APIException if the Convertio API returns an error
     * @throws \Convertio\Exceptions\CURLException if there is a general HTTP / network error
     *
     */
    public function delete()
    {
        $this->api->delete('/convert/'.$this->convert_id, false);
        return $this;
    }
}
