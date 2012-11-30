<?php

include_once("includes/php/fbmain.php");
include_once("includes/php/funcoes.php");
include_once("../../iqdirect/includes/config.inc.php");


global $perfil_nome, $perfil_email, $perfil_foto, $perfil_link, $perfil_id;
global $id_resposta;

$perfil_nome 	= $_POST["perfil_nome"];
$perfil_email 	= $_POST["perfil_email"];
$perfil_foto 	= $_POST["perfil_foto"];
$perfil_link 	= $_POST["perfil_link"];
$perfil_id 		= $_POST["perfil_id"];

$id_resposta	= $_POST["id_resposta"];


// quantos registros por página vai ser mostrado
$registro_por_pagina = 10;



switch($op){
	case "cadastra_resposta":
		f_cadastra_resposta();
	break;
	
	case "cadastra_voto":
		f_cadastra_voto();
	break;
	
	case "apagar_resposta":
		f_apagar_resposta($id);
	break;
	
	case "busca":
		f_busca();
	break;
	
	case "pesquisa_ajax":
		f_pesquisa_ajax();
	break;
	
	default:
		f_default();
	break;
}





/****************************** Funções ******************************/

function f_default($first_time = true)
{
	global $facebook, $user;
	global $perfil_nome, $perfil_email, $perfil_foto, $perfil_link, $perfil_id;
	global $id_resposta, $respostas, $msgerro, $ancora;
	global $where, $anterior, $numeracao, $proxima, $pg, $registro_por_pagina;
	
	#$msgerro = "<font color='#FFA500' face='tahoma'>A promoção está encerrada.</font>";
	
	$first_time = true;
	echo $first_time."<br>";
	
	
	// só pega os dados na primeira vez que ele entrar no aplicativo
	if($first_time)
	{
		//se o usuário está logado em uma sessão válida
		if ($user)
		{
			try
			{
				$fql = "SELECT name, email, pic_square, profile_url FROM user WHERE uid=".$user;
				
				// referência em: http://developers.facebook.com/docs/reference/fql/
				$param = array('method'   => 'fql.query',
							   'query'    => $fql,
							   'callback' => '');
				
				$fqlResult = $facebook->api($param);
				
				$perfil_nome  = $fqlResult[0]["name"];
				$perfil_email = $fqlResult[0]["email"];
				$perfil_foto  = $fqlResult[0]["pic_square"];
				$perfil_link  = $fqlResult[0]["profile_url"];
				$perfil_id	  = $user;
			}
			catch(Exception $o){
				d($o);
			}
		}
    }
    
    
	/*
    //set page to include default is home.php
    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : "home.php";
    include_once "template.php";
	*/

	
	if(isset($pg))
         $where = base64_decode($where);


	$DB_1  = new Class_DB;
	$DB_1->connect("FBipirangashop", "cont.senderdirect.com", "root", "rmc3284K");

	$DB_1->query("SELECT * FROM respostas $where");
	
	$quantreg = $DB_1->affected_rows();
	
	
	
	//***** Paginacao
	$numreg = $registro_por_pagina; // quantos registros por página vão ser mostrados
	
	if (!isset($pg))
		$pg = 0;
	
	$inicial = $pg * $numreg;
	//*********************************
	
	f_monta_paginacao("home", $pg, $quantreg, $numreg);
	
	
	

	
	$query = "SELECT id, nome, email, resposta, votos, data_cadastro, url_img, profile
			  FROM respostas
			  $where
			  ORDER BY data_cadastro DESC
			  LIMIT $inicial, $numreg";
	
	$DB_1->query($query);
	



	while($DB_1->next_record()){
		$nome 			= $DB_1->f("nome");
		$id_resposta	= $DB_1->f("id");
		$email			= $DB_1->f("email");
		$resposta 		= $DB_1->f("resposta");
		$votos 			= $DB_1->f("votos");
		$data_cadastro 	= $DB_1->f("data_cadastro");
		#$url_img	 	= $DB_1->f("url_img");
		#$profile		= $DB_1->f("profile");
		
		$url_img	 	= str_replace("http://profile.ak.fbcdn.net", "https://fbcdn-profile-a.akamaihd.net", $DB_1->f("url_img"));
		$url_img	 	= str_replace("http://", "https://", $url_img);
		$profile		= str_replace("http://", "https://", $DB_1->f("profile"));
		
		if($votos == 1)
			$votos = $votos." voto";
		else
			$votos = $votos." votos";
		
		$momento_envio = f_momento_envio($data_cadastro);
		
		if($email == $perfil_email)
			//$opcao_apagar = "<a href=\"?op=apagar_resposta&id=$id\">apagar</a>";
			$opcao_apagar = "<button onclick=\"f_apaga_resposta('$id_resposta')\" type=\"button\">&nbsp;apagar&nbsp;</button>";
		else
			$opcao_apagar = "";
		
		$botao_votar = "<div onclick=\"f_cadastra_voto('$id_resposta')\" class=\"votar\"></div>";
		
		$respostas .= "<div class=\"exiberesposta\">
						   <div class=\"interno\">
							   <div class=\"geral\">
								   <a href=\"$profile\" style=\"text-decoration:none\" target=\"blank\"><img src=\"$url_img\" width=\"67\" height=\"67\" /></a>
								   <a href=\"$profile\" style=\"text-decoration:none\" target=\"blank\"><h1>$nome</h1></a>
								   <p>$resposta</p>
								   <p class=\"stats\">$votos - $momento_envio</p>
								   <p class=\"stats\">$opcao_apagar</a>
							   </div>
							   $botao_votar
						   </div>
					   </div>";
	}
    
    echo(template_offline("inicio.html"));
}


function f_cadastra_resposta()
{
	global $facebook;
	global $perfil_nome, $perfil_email, $perfil_foto, $perfil_link, $perfil_id;
	global $resposta, $msgerro, $ancora;
	
	//$msgerro = "<font color='#FFA500' face='tahoma'>O envio de frases está encerrado. Continue votando!</font>";
	
		
	if($perfil_nome <> "")
	{
		if(trim($resposta) <> ""){
			$DB = new Class_DB;
			$DB->connect("FBipirangashop", "cont.senderdirect.com", "root", "rmc3284K");
			
			
			// para não dar erro de banco de dados na tela
			$resposta = str_replace("'", "", $resposta);
			$resposta = str_replace("\"", "", $resposta);
			$resposta = str_replace("\\", "", $resposta);
			
			$DB->query("SELECT count(*) FROM respostas WHERE email = '".$perfil_email."'");
			
			$DB->next_record();
				
			$verifica = $DB->f(0);
		
			if($verifica == 0)
			{			
				$DB->query("INSERT INTO respostas
							(nome, email, resposta, data_cadastro, url_img, profile)
							VALUES
							('".utf8_decode($perfil_nome)."', '".$perfil_email."', '".$resposta."', NOW(), '".$perfil_foto."', '".$perfil_link."')");
							
				if($perfil_id <> "")
				{
					$permissions = $facebook->api("/me/permissions");
					
					// verificando se o usuario deu permissão para postar no mural dele
					if(array_key_exists('publish_stream', $permissions['data'][0])){
						$args = array('message'	=> 'Estou concorrendo a uma viagem sensacional pelo aplicativo da MSC Cruzeiros.',
									  'link'	=> 'https://apps.facebook.com/msccruzeiros');
						
						//$post_id = $facebook->api("/".$perfil_id."/feed", "post", $args);
						$post_id = $facebook->api("/me/feed", "post", $args);
					}
					else{
						//header( "Location: " . $facebook->getLoginUrl(array("scope" => "publish_stream")) );
					}				
				}
			}
			else{
				$msgerro = "<font color='#FFA500' face='tahoma'><b><u>Erro:</u></b> <i>Só é permitida uma resposta por participante!</i></font>";
			}
			
			$ancora = "<a name=\"votacao\"></a>";
		}	
	}
	
	
	f_default(false);
}


function f_cadastra_voto()
{
	global $perfil_nome, $perfil_email, $perfil_foto, $perfil_link, $perfil_id;
	global $id_resposta, $msgerro, $ancora;
	
	/*	
	$DB = new Class_DB;
	$DB->connect("FBipirangashop", "cont.senderdirect.com", "root", "rmc3284K");
	
	if($perfil_email <> ""){
		$DB->query("SELECT count(*)
					FROM votos
					WHERE email  	  = '".$perfil_email."'
					AND   id_resposta = $id_resposta");
					
		$DB->next_record();
		
		$verifica = $DB->f(0);
		
		if($verifica == 0){
			$DB->query("UPDATE respostas
						SET votos = votos + 1
						WHERE id = $id_resposta");
						
			$DB->query("INSERT INTO votos
						(id_resposta, email, data_cadastro)
						VALUES
						($id_resposta, '".$perfil_email."', NOW())");
		}
		else{
			$msgerro = "<font color='#FFA500' face='tahoma'><b><u>Erro:</u></b> <i>Voto já computado para esta resposta!</i></font>";
		}
		
		$ancora = "<a name=\"votacao\"></a>";
	}
	*/
	
	f_default(false);
}


function f_apagar_resposta($id_resposta)
{
	global $perfil_nome, $perfil_email, $perfil_foto, $perfil_link, $perfil_id;
	global $id_resposta, $ancora;
	
		
	$DB = new Class_DB;
	$DB->connect("FBipirangashop", "cont.senderdirect.com", "root", "rmc3284K");
	
	// antes de deletar, verifica se a pessoa que quer deletar é o dono da resposta
	$DB->query("SELECT count(*) FROM respostas
				WHERE email = '".$perfil_email."'
				AND   id = '$id_resposta'");
	
	$DB->next_record();
	
	$verifica = $DB->f(0);
	
	if($verifica > 0){
		$DB->query("DELETE FROM respostas WHERE id = $id_resposta");
		
		$DB->query("DELETE FROM votos WHERE id_resposta = $id_resposta");
		
		$ancora = "<a name=\"votacao\"></a>";
	}
	
	f_default(false);
}


function f_busca()
{
	global $perfil_nome, $perfil_email, $perfil_foto, $perfil_link, $perfil_id;
	global $func;
	global $where;
	
	if($func == "home")	
    {	
    	global $course, $busca_data_cadastro_de, $busca_data_cadastro_ate;
		
		if($course <> "PESQUISAR PARTICIPANTE")
			$course = trim($course);
		else
			$course = "";
		
		$where .= "WHERE nome LIKE '%".$course."%' ";
		
		f_default(false);
	}
}


function f_pesquisa_ajax()
{
	global $valor;
	
	$DB = new Class_DB;
	$DB->connect("FBipirangashop", "cont.senderdirect.com", "root", "rmc3284K");
	$DB->query("SELECT nome FROM respostas WHERE nome LIKE '$valor%'");
				
	if($DB->affected_rows())
	{
		while($DB->next_record())
		{
			echo $DB->f(0)."\n";
		}
	}
	
}
?>