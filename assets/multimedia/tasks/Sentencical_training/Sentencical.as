﻿package{		import flash.display.MovieClip;	import flash.events.*	import flash.net.*;	import flash.text.*;	import flash.utils.*;	import flash.filters.*;	import flash.geom.*;	import StopWatch;		public class Sentencical extends MovieClip{				private var tmp_sentences:Array = new Array();				var nc:NetConnection = new NetConnection();		var url:String = "";				private var startTimeSentence:Date;		private var endTimeSentence:Date;		private var startTimeQuestion:Date;		private var endTimeQuestion:Date;		private var time:Number = 0;				private var _userID:String = "0000";		private var _score:Number = 0;		private var session:String = "";		private var correctCounter:uint = 1;		private var userAnswer:uint = 0;		private var roundWatch:StopWatch = new StopWatch(30);		private var taskWatch:StopWatch;				private var thumbsup:Thumbsup = new Thumbsup();				private var sentences:Array = new Array();		private var currSentence:Array = new Array();		private var sentenceCounter:uint = 0;				private var dropShadow:DropShadowFilter = new DropShadowFilter();		private var color:ColorTransform = new ColorTransform();		private var glowFilter:GlowFilter = new GlowFilter();		private var writer:Timer;						public function Sentencical():void{			Yes.visible = false;			No.visible = false;			Continue.selectable = false;			Yes.selectable = false;			No.selectable = false;			greatText.visible = false;						if(root.loaderInfo.parameters.user_id != null){				_userID = root.loaderInfo.parameters.user_id;			}else{				_userID = "2222session01";			}			if(root.loaderInfo.parameters.session != null){				session = root.loaderInfo.parameters.session;			}else{				session = "2";			}						if(root.loaderInfo.parameters.url != null){				url = root.loaderInfo.parameters.url;			}else{				url = "http://localhost:8888/damlab/";				//url = "http://thehygeneproject.org/damlab/";			}			if(root.loaderInfo.parameters.time != null){				time = root.loaderInfo.parameters.time;			}else{				time = 300; //5 minutes			}						roundWatch.addEventListener('stopWatchComplete',roundTimeUp);			taskWatch = new StopWatch(time);			taskWatch.addEventListener('stopWatchComplete',taskTimeUp);						var responder:Responder = new Responder(onResultGet,onError);			nc.connect(url + "index.php/amf_gateway");			trace(nc.connected);			nc.call("service_sentencical_training.get_sentences",responder,_userID);					}				private function onResultGet(e:Object):void{			sentences = e as Array;			trace(e);			Continue.text = "START";			Continue.addEventListener(MouseEvent.MOUSE_DOWN,startGame);		}			private function startGame(evt:MouseEvent):void{			var user_data:Object = new Object();			user_data.user_id = _userID;			user_data.session = session;			user_data.time = getMySQLdate(new Date());						var responder:Responder = new Responder(onResult,onError);			nc.connect(url + "index.php/amf_gateway");			nc.call("service_session_info.start",responder, user_data);			taskWatch.start();			Continue.removeEventListener(MouseEvent.MOUSE_DOWN,startGame);			Continue.text = "CONTINUE";			displaySentence(null);		}		private function shuffle(a,b):int {			var num : int = Math.round(Math.random()*2)-1;			return num;		}					private function onIOError(evt:IOErrorEvent):void{			trace(evt.target.error);		}				private function displaySentence(evt:Event):void{			roundWatch.reset();			startTimeSentence = new Date();			dropShadow.hideObject = false;			theSentence.text = "";			greatText.visible = false;			thumbsup.removeEventListener(MouseEvent.MOUSE_DOWN,displaySentence);			thumbsup.visible = false;			Yes.visible = false;			No.visible = false;			Continue.visible = true;			currSentence = sentences[sentenceCounter].sentence.split("");			writer = new Timer(50,currSentence.length+1);			writer.addEventListener(TimerEvent.TIMER,writeLetter);			writer.start();					}				private function writeLetter(evt:TimerEvent):void{			if(currSentence.length == 0){				Continue.addEventListener(MouseEvent.MOUSE_OVER,adjustColor);				Continue.addEventListener(MouseEvent.MOUSE_OUT,resetColor);				Continue.addEventListener(MouseEvent.MOUSE_UP,onClickUp);			}else{				theSentence.appendText(currSentence[0]);				currSentence = currSentence.slice(1,currSentence.length);			}		}		private function adjustColor(evt:MouseEvent):void{			color.redMultiplier = -100;			color.blueMultiplier = -100;			color.greenMultiplier = -100;			Continue.transform.colorTransform = color;		}		private function resetColor(evt:MouseEvent):void{			Continue.filters = [dropShadow];			color.redMultiplier = 100;			color.blueMultiplier = 100;			color.greenMultiplier = 100;			Continue.transform.colorTransform = color;		}		private function onClickUp(evt:MouseEvent):void{			endTimeSentence = new Date();			startTimeQuestion = new Date();			Continue.removeEventListener(MouseEvent.MOUSE_OVER,adjustColor);			Continue.removeEventListener(MouseEvent.MOUSE_OUT,resetColor);			Continue.removeEventListener(MouseEvent.MOUSE_UP,onClickUp);			writer.removeEventListener(TimerEvent.TIMER,writeLetter);			theSentence.text = sentences[sentenceCounter].question;						color.redMultiplier = 100;			color.blueMultiplier = 100;			color.greenMultiplier = 100;			Continue.transform.colorTransform = color;			Continue.visible = false;						Yes.filters = [dropShadow];			No.filters = [dropShadow];			Yes.visible = true;			No.visible = true;									Yes.addEventListener(MouseEvent.MOUSE_OVER,mouseOverAnswer);			Yes.addEventListener(MouseEvent.MOUSE_OUT,resetAnswer);			Yes.addEventListener(MouseEvent.MOUSE_DOWN,getAnswer);						No.addEventListener(MouseEvent.MOUSE_OVER,mouseOverAnswer);			No.addEventListener(MouseEvent.MOUSE_OUT,resetAnswer);			No.addEventListener(MouseEvent.MOUSE_DOWN,getAnswer);		}		private function getAnswer(evt:MouseEvent):void{			endTimeQuestion = new Date();			dropShadow.hideObject = true;			evt.target.filters = [dropShadow,glowFilter];						Yes.removeEventListener(MouseEvent.MOUSE_OVER,mouseOverAnswer);			Yes.removeEventListener(MouseEvent.MOUSE_OUT,resetAnswer);			Yes.removeEventListener(MouseEvent.MOUSE_DOWN,getAnswer);						No.removeEventListener(MouseEvent.MOUSE_OVER,mouseOverAnswer);			No.removeEventListener(MouseEvent.MOUSE_OUT,resetAnswer);			No.removeEventListener(MouseEvent.MOUSE_DOWN,getAnswer);						if((sentences[sentenceCounter].answer == "Y" && evt.target.text == "YES") ||				(sentences[sentenceCounter].answer == "N" && evt.target.text == "NO")){								userAnswer = 1;								if(correctCounter == 10){					_score += 40;					Score.text = String(_score);					sendData();					sentenceCounter++;					correctCounter = 1;					var greatJobTimer:Timer = new Timer(500,1);					greatJobTimer.addEventListener(TimerEvent.TIMER, showGreatJob);					greatJobTimer.start();					return;				}else if(correctCounter > 4){					_score += 40;					correctCounter++;				}else{					_score += 10*correctCounter++;				}				Score.text = String(_score);							}else{				userAnswer = 0;				correctCounter = 1;			}			sendData();						sentenceCounter++;						var nextSentence:Timer = new Timer(500,1);			nextSentence.addEventListener(TimerEvent.TIMER,displaySentence);			nextSentence.start();		}				private function showGreatJob(evt:Event):void{			theSentence.text = "";			Yes.visible = false;			No.visible = false;			greatText.visible = true;			thumbsup.x = 127;			thumbsup.y = 10;			thumbsup.visible = true;			addChild(thumbsup);			thumbsup.addEventListener(MouseEvent.MOUSE_DOWN,displaySentence);		}				private function mouseOverAnswer(evt:MouseEvent):void{			glowFilter.color = 0xffff00;			glowFilter.strength = 500;			glowFilter.blurX = 10			glowFilter.blurY = 10;			evt.target.filters = [dropShadow,glowFilter];		}		private function resetAnswer(evt:MouseEvent):void{			evt.target.filters = [dropShadow];		}				private function sendData():void{			//var sal:SendAndLoad = new SendAndLoad();			var userVars:Object = new Object();			userVars.user_id = _userID;			userVars.sentence_id = sentences[sentenceCounter].sentenceID;			userVars.user_answer = String(userAnswer);			if(sentences[sentenceCounter].answer == "Y"){				userVars.cor_answer = String(1);			}else{				userVars.cor_answer = String(0);			}			userVars.start_time_sentence = getMySQLdate(startTimeSentence);			userVars.end_time_sentence = getMySQLdate(endTimeSentence);			userVars.start_time_question = getMySQLdate(startTimeQuestion);			userVars.end_time_question = getMySQLdate(endTimeQuestion);			userVars.score = String(_score);			userVars.session = session;						//sal.sendData(url+"/scripts/php/insertData.php",userVars);						var responder:Responder = new Responder(onResult,onError);			nc.connect(url+"index.php/amf_gateway");			trace("sending sentences");			nc.call("service_sentencical_training.set_sentences",responder,_userID,sentences.slice(sentenceCounter+1,sentences.length));			nc.call("service_sentencical_training.insert",responder,userVars);				}		private function onResult(e:Object):void{			trace(e)		}		private function onError(e:Object):void{			trace("error" + e);		}				public function getMySQLdate(flashDate:Date):String{		   var year:String;var month:String;var day:String;var hours:String;		   var minutes:String;var seconds:String;				   year = String(flashDate.fullYear);				   if((flashDate.month+1)<10){			   month="0"+String(flashDate.month+1);			}else{				month=String(flashDate.month+1);			}		   if(flashDate.date<10){			   day="0"+String(flashDate.date);			}else{				day=String(flashDate.date);			}		   if(flashDate.getHours()<10){			   hours="0"+String(flashDate.getHours())			}else{				hours=String(flashDate.getHours());			}		   if(flashDate.getMinutes()<10){			   minutes="0"+String(flashDate.getMinutes());			}else{				minutes=String(flashDate.getMinutes());			}		   if(flashDate.getSeconds()<10){			   seconds="0"+String(flashDate.getSeconds());			}else{				seconds=String(flashDate.getSeconds());			}		   var date:String=(year+"-"+month+"-"+day+" "+hours+":"+minutes+":"+seconds);		   return date;  		}		private function roundTimeUp(evt:Event):void{						var user_data:Object = new Object();			user_data.user_id = _userID;			user_data.session = session;			var responder:Responder = new Responder(onResult,onError);			nc.connect(url + "index.php/amf_gateway");			nc.call("service_sentencical_training.delete_session", responder, user_data);						navigateToURL(new URLRequest(url+"index.php/increaseintellect/logout"),"_self");		}		private function taskTimeUp(evt:Event):void{			roundWatch.stop();			Yes.removeEventListener(MouseEvent.MOUSE_OVER,mouseOverAnswer);			Yes.removeEventListener(MouseEvent.MOUSE_OUT,resetAnswer);			Yes.removeEventListener(MouseEvent.MOUSE_DOWN,getAnswer);						No.removeEventListener(MouseEvent.MOUSE_OVER,mouseOverAnswer);			No.removeEventListener(MouseEvent.MOUSE_OUT,resetAnswer);			No.removeEventListener(MouseEvent.MOUSE_DOWN,getAnswer);			Continue.removeEventListener(MouseEvent.MOUSE_OVER,adjustColor);			Continue.removeEventListener(MouseEvent.MOUSE_OUT,resetColor);			Continue.removeEventListener(MouseEvent.MOUSE_UP,onClickUp);			writer.removeEventListener(TimerEvent.TIMER,writeLetter);			theSentence.text = "The training session is complete.  \nYou will be redirected to the task index shortly.  \nThank you for participating.";						var user_data:Object = new Object();			user_data.user_id = _userID;			user_data.session = session;			user_data.type = 'sentencical_training_status';			user_data.time = getMySQLdate(new Date());						var responder:Responder = new Responder(onResult,onError);			nc.connect(url + "index.php/amf_gateway");			nc.call("service_session_info.update",responder, user_data);						var t:Timer = new Timer(5000,1);			t.addEventListener(TimerEvent.TIMER,redirectParticipant);			t.start();		}		private function redirectParticipant(evt:TimerEvent):void{			navigateToURL(new URLRequest(url+"index.php/increaseintellect/training/"),"_self");					}	}}