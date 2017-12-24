<?php
	/*
		Puerta de entrada de la API Turnos - Telegram
	*/
	
	require_once("../controllers/TurnosController.php");
	require_once("../models/ModelTurnos.php");


	TurnosController::getInstance()->mensajeBot();
