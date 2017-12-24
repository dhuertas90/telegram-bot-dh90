<?php
	/*
		Puerta de entrada de la API Turnos - Telegram
	*/
	
	require_once("../controllers/TurnosController.php");


	TurnosController::getInstance()->mensajeBot();
