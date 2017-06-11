<?php
/**
 * This file contains code about \Convertio\Convertio class
 */
namespace Convertio;

use Exception;
use Convertio\Exceptions\APIException;
use Convertio\Exceptions\CURLException;

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
     * Contain result file's. More info: https://convertio.co/api/docs/
     * @var string
     */
    public $result_content;

    /**
     * Contain size in bytes of result file. More info: https://convertio.co/api/docs/
     * @var integer
     */
    public $result_size;

    /**
     * Construct a new Convertio instance
     *
     * @param string $api_key API Key of your application. You can get your API Key on https://convertio.co/api/
     * @param array $settings Allows overriding of default API wrapper parameters (http protocol, timeouts)
     * @return \Convertio\Convertio
     *
     * @throws \Convertio\Exceptions\APIException if api key is missing or empty or settings is incorrect
     */
    public function __construct($api_key = null, $settings = array())
    {
        $this->api = new API($api_key);

        $this->settings($settings);

        return $this;
    }

    /**
     * Override default API wrapper parameters
     *
     * @param array $settings Allows overriding of default API wrapper parameters (http protocol, timeouts)
     * @return \Convertio\Convertio
     *
     * @throws \Convertio\Exceptions\APIException if api key is missing or empty or settings is incorrect
     */
    public function settings($settings = array())
    {
        foreach ($settings as $property => $value) {
            $this->api->__set($property, $value);
        }

        return $this;
    }

    /**
     * This method is used to set wrapper config variables, i.e.:
     * $Convertio->__set('convert_id','32-char-long-string')
     *
     * @param string $property one of the wrapper private property
     * @param string $value the value of the property
     * @return mixed
     *
     * @throws \Convertio\Exceptions\APIException if the value of property is incorrect
     */
    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            if (($property == 'convert_id') && (strlen($value) != 32)) {
                throw new APIException("Convert ID is a 32 characters long string");
            }
            $this->$property = $value;
        }

        return $this;
    }


    /**
     * This method is used to get the Conversion ID and use it in another API instance
     *
     * @return string
     */
    public function getConvertID()
    {
        return $this->convert_id;
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
     * Starts new conversion with custom data.
     *
     * @param string|resource|array $data parameters for the conversion
     * @return \Convertio\Convertio
     *
     * @throws \Exception
     * @throws \Convertio\Exceptions\APIException if the Convertio API returns an error
     * @throws \Convertio\Exceptions\CURLException if there is a general HTTP / network error
     *
     */
    public function rawStart($data)
    {
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
     * Starts new conversion from local file
     *
     * @param string $input_fn path to local input file
     * @param string $output_format output format. You can view available formats on https://convertio.co/formats/
     * @param array $options conversion options. You can view available options on https://convertio.co/api/docs/
     * @return \Convertio\Convertio
     *
     * @throws \Exception
     * @throws \Convertio\Exceptions\APIException if the Convertio API returns an error
     * @throws \Convertio\Exceptions\CURLException if there is a general HTTP / network error
     *
     */
    public function start($input_fn, $output_format, $options = array())
    {
        $data = array();
        $data['input'] = 'upload';
        $data['outputformat'] = $output_format;
        $data['options'] = $options;

        $this->rawStart($data);

        if ($this->step == 'error') {
            return $this;
        }

        if (!file_exists($input_fn)) {
            throw new \Exception("Failed to open stream. No such file: ".$input_fn);
        }

        $fp = fopen($input_fn, 'r');
        $this->api->put('/convert/' . $this->convert_id . '/' . basename($input_fn), $fp);
        fclose($fp);

        return $this;
    }


    /**
     * Starts new conversion from remote url
     *
     * @param string $url URI of input file or web-page
     * @param string $output_format output format. You can view available formats on https://convertio.co/formats/
     * @param array $options conversion options. You can view available options on https://convertio.co/api/docs/
     * @return \Convertio\Convertio
     *
     * @throws \Exception
     * @throws \Convertio\Exceptions\APIException if the Convertio API returns an error
     * @throws \Convertio\Exceptions\CURLException if there is a general HTTP / network error
     *
     */
    public function startFromURL($url, $output_format, $options = array())
    {
        $data = array();
        $data['input'] = 'url';
        $data['file'] = $url;
        $data['outputformat'] = $output_format;
        $data['options'] = $options;

        return $this->rawStart($data);
    }


    /**
     * Starts new conversion from raw content. Base64 is needed as JSON is not binary safe.
     * We will use PUT method for raw content in future.
     *
     * @param string $content converting file's content.
     * @param string $input_format input format. You can view available formats on https://convertio.co/formats/
     * @param string $output_format output format. You can view available formats on https://convertio.co/formats/
     * @param array $options conversion options. You can view available options on https://convertio.co/api/docs/
     * @return \Convertio\Convertio
     *
     * @throws \Exception
     * @throws \Convertio\Exceptions\APIException if the Convertio API returns an error
     * @throws \Convertio\Exceptions\CURLException if there is a general HTTP / network error
     *
     */
    public function startFromContent($content, $input_format, $output_format, $options = array())
    {
        $data = array();
        $data['input'] = 'base64';
        $data['file'] = base64_encode($content);
        $data['filename'] = 'raw.' . $input_format;
        $data['outputformat'] = $output_format;
        $data['options'] = $options;

        return $this->rawStart($data);
    }

    /**
     * Fetch result file's content
     *
     * @return \Convertio\Convertio
     *
     * @throws \Exception
     * @throws \Convertio\Exceptions\APIException if the Convertio API returns an error
     * @throws \Convertio\Exceptions\CURLException if there is a general HTTP / network error
     *
     */
    public function fetchResultContent()
    {
        $this->data = $this->api->get('/convert/'.$this->convert_id.'/dl/base64', false);
        $this->result_content = @base64_decode($this->data['data']['content']);

        if (empty($this->result_content)) {
            $this->step = 'error';
            $this->error_message = 'Empty result file';
            throw new Exceptions\APIException($this->error_message);
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
        $this->fetchResultContent();

        if (file_put_contents($local_fn, $this->result_content) === false) {
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
