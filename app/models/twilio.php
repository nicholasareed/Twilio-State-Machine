<?
	class Twilio extends AppModel {
		// Twilio numbers

		var $name = 'Twilio';
		
		var $useTable = 'twilios';
		
		// Relationships
		
		var $belongsTo = array('Event');	


		// Functions

		function auth_incoming(){
			// Authenticate incoming SMS from Twilio
			
		}


		function segment_msg($text = ''){
			// Segment an outgoing message into multiple replies (160 char max)

			$segs = array(); // Message segments array, each no more than 160 characters

			$len = strlen($text);
			if($len > 160){
				$i = 0;
				$j = 0;
				$go = 1;
				while($go){
					$t = substr($text,$j,160);
					$segs[$i] = $t;
					$j += 160;
					$i++;
					if($j > $len || $i > 5){
						$go = 0;
					}
				}
			} else {
				$segs[] = $text;
			}

			return $segs;
		}


		function reply($text){
			// Reply to 
			// Separate __gt 160 character requests into multiple SMS messages
			$segs = $this->segment_msg($text);

			$reply = "<Response>";
							
			foreach($segs as $text){
				$reply .= "	<Sms>".$text."</Sms>";
			}
			
			$reply .= "</Response>";

			return $reply;
		}


		function send_msg($from_ptn = '', $to_ptns = array(), $text = ''){
			// Handles same message to multiple Recipients

			// Turn $to_ptns into an array
			if(!is_array($to_ptns)){
				$to_ptns = array($to_ptns);
			}

			// Twilio settings
			$id = TWILIO_ID;
			$sid = TWILIO_SID;
			$token = TWILIO_TOKEN;
			$url = 'https://'.$id.':'.$token.'@api.twilio.com/2010-04-01/Accounts/'.$sid.'/SMS/Messages';

			// Separate __gt 160 character requests into multiple SMS messages
			$segs = $this->segment_msg($text);

			$Curl =& ClassRegistry::init('Curl');

			foreach($to_ptns as $to_ptn){
				$results = array();
				foreach($segs as $body){
					$From = $from_ptn;
					$To = $to_ptn;

					// Format $To nicely
					$To = $this->formatTo($To);
					if(empty($To)){
						// Bad PTN, don't even try sending
						continue;
					}

					$Body = substr($body,0,160);
					
					$Curl->url = $url;
					$Curl->post = true;
					$Curl->postFieldsArray = compact('From','To','Body');
					$results[] = array('request' => $Body,
									   'result' => $Curl->execute()); // Add result to array
				}
			}

			// Array or Twilio results	
			// Log this! (json_encoded or something?)

			return $results;
								
		}


		function formatTo($to = ''){
			// Format a Phone Number

			if(strlen($to) == 10){
				// Add "+1"
				return '+1'.$to;
			}
			if(strlen($to) == 11){
				// Add "+"
				return '+'.$to;
			}
			if(strlen($to) == 12){
				return $to;
			}

			return '';

		}
	  
	}

?>