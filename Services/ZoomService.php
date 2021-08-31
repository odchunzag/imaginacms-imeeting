<?php

namespace Modules\Imeeting\Services;

// Traits
use Modules\Imeeting\Traits\ZoomJwt;

class ZoomService
{

	use ZoomJwt;


	/**
	* @param Email - email (required)
	* @param DateTime - startTime (required)
	* @param String - topic (optional)
	* @param int - duration (optional)
	*/
	public function create($dataRequest){

		// Default values
		$defaultConf = $this->getConfig('asgard.imeeting.config.providers.zoom.defaulValuesMeeting');
		
		// Get data Meeting
		$data = $dataRequest['meetingAttr'];

		// Testing Find and Create User
		// Only Pro or higher plan
		/*
		$existUser = $this->findUser($data['email']);
		if(!isset($existUser->id)){
			dd($this->createUser($data['email']));
		}
		*/

		
		// 'me' => Esto lo asignaria con el mismo host siempre
		//$path = 'users/me/meetings';

		$path = 'users/'.$data['email'].'/meetings';

		$body = [
			'topic' => $data['title'] ?? $defaultConf['topic'],
            'type' => 2,// Shedule Meeting
            'start_time' => $this->convertTimeFormat($data['startTime']),
            'duration' => $data['duration'] ?? $defaultConf['duration'], // En min
            'settings' => [
                'host_video' => false,//Start the video when the host join meeting
                'participant_video' => false,//Start the video when participans join meeting
                'waiting_room' => true,
            ]
		];

		try {
			// Request
	        $response = $this->requestPost($path,$body,$data);


	        // Format data to save
	        //$dataFormat = $this->formatResponse(json_decode($response->body()));

	        $resultResponse = json_decode($response->body());

	        if(isset($resultResponse->id)){
	        	$result = $this->formatResponse($resultResponse);
	        	//return $this->formatResponse($resultResponse);
	        }else{
	        	throw new \Exception($resultResponse->message, 500);
	        	//return $resultResponse;
	        }

	    } catch (\Exception $e) {
            \Log::error($e->getMessage());
            $status = 500;
            $result = [
                'errors' => $e->getMessage()
            ];
        }
       
        return $result;
        

	}

	/**
	* @param Response from Provider
	*/
	private function formatResponse($response){

		$data["provider_name"] = "zoom";
		$data["provider_meeting_id"] = $response->id;
		$data["star_url"] = $response->start_url;
		$data["join_url"] = $response->join_url;
		$data["password"] = $response->password; //opcional
		
		// Extra Attr
		// $data["options"]
		
		return $data;

	}

	/**
	* @param email from user (Host)
	* @return object id | object code=1001 - message="not found" 
	*/
	public function findUser($email){

		$path = 'users/'.$email;
		$body = [];
		$data = [];

		try {

			// Request
	        $response = $this->requestGet($path,$body,$data);

	        $result = json_decode($response->body());

	    } catch (\Exception $e) {
            \Log::error($e->getMessage());
            $status = 500;
            $result = [
                'errors' => $e->getMessage()
            ];
        }
       
        return $result;

	}

	/**
	* @param Only Pro or higher plan
	* @return object user
	*/
	public function createUser($email){

		$path = 'users';
		$body = [];
		$data = [];

		$body = [
			'action' => 'create',
            'user_info' => [
                'email' => $email,
			    'type'=> 1, //Basic
			    'first_name' => "Prueba",
			    'last_name'=> "Perez"
            ]
		];

		try {

			// Request
	        $response = $this->requestPost($path,$body,$data);

	        $result = json_decode($response->body());

	    } catch (\Exception $e) {
            \Log::error($e->getMessage());
            $status = 500;
            $result = [
                'errors' => $e->getMessage()
            ];
        }
       
        return $result;

	}
   

}