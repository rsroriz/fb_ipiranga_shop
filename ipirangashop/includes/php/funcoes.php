<?php

function f_momento_envio($data_cadastro){
	$ano = substr($data_cadastro, 0, 4);
	$mes = substr($data_cadastro, 5, 2);
	$dia = substr($data_cadastro, 8, 2);
	
	$hor = substr($data_cadastro, 11, 2);
	$min = substr($data_cadastro, 14, 2);
	
	switch($mes){
		case "01":
			$mes = "Janeiro";
		break;
		case "02":
			$mes = "Fevereiro";
		break;
		case "03":
			$mes = "Março";
		break;
		case "04":
			$mes = "Abril";
		break;
		case "05":
			$mes = "Maio";
		break;
		case "06":
			$mes = "Junho";
		break;
		case "07":
			$mes = "Julho";
		break;
		case "08":
			$mes = "Agosto";
		break;
		case "09":
			$mes = "Setembro";
		break;
		case "10":
			$mes = "Outubro";
		break;
		case "11":
			$mes = "Novembro";
		break;
		case "12":
			$mes = "Dezembro";
		break;
	}
	
	return "enviado em ".$dia." de ".$mes." de ".$ano." às ".$hor.":".$min;
}

function template_offline($nome_arquivo)
{
	global $templates_path;
	
	$templates_path = "includes/html";
	
	$output="";
	$filename = "$templates_path/$nome_arquivo";
	$handle = fopen($filename, "r");
	if ($handle) 
	{
		while (!feof($handle))
		{	
			$buffer = fgets($handle, 4096);
			while((strpos($buffer,"<var=")>0) and (strpos($buffer,"</var>")>0))
			{
				$temp = substr($buffer,strpos($buffer,"<var="),strpos($buffer,"</var>")-strpos($buffer,"<var=")+6);
				$nomevar = trim(substr($temp,strpos($temp,"=")+1,strpos($temp,">")-strpos($temp,"=")-1));
				$buffer = substr($buffer,0,strpos($buffer,"<var=")).$GLOBALS[$nomevar].substr($buffer,strpos($buffer,"</var>")+6);				
			}
			$output.= $buffer;
		}
		fclose($handle); 
	}
	return($output);	
}

function  f_monta_paginacao($func,$pg, $quantreg, $numreg){
	global $anterior,$numeracao,$proxima;
	global $where, $ident;
	
	$quant_pg = ceil($quantreg/$numreg);
	$quant_pg++;
        
	if ( $pg > 0) 
		$anterior =  "<a href=\"?op=$func&pg=".($pg-1)."&where=".base64_encode($where)."&ident=$ident#votacao\" class=\"next\"><b><font>&laquo; </font></b></a>";
	else 
		$anterior =  "<a color=#CCCCCC href='#'>&laquo; </a>";
         
	if(($quant_pg>1) and ($quant_pg<=10))
	{
		for($i_pg = 1; $i_pg < $quant_pg; $i_pg++) 
		{ 
			if ($pg == ($i_pg-1))  
				$numeracao .=  "<a href='#'>$i_pg</a> ";
			else 
			{ 
				$i_pg2 = $i_pg-1;
				$numeracao .= " <a href=\"?op=$func&pg=$i_pg2&where=".base64_encode($where)."&ident=$ident#votacao\" class=\"next\"><b>$i_pg</b></a> ";
			}
		}
	}
	else if ($quant_pg > 11)
	{
		$inicio = $pg-4;
		$final = $pg+4;

		if ($pg >= 10)
		{
			$limite_menor = $inicio-1;
			$i_pg2 = $limite_menor - 1;
			if($limite_menor < 0)
			{
				$limite_menor = 1;
			}
			$numeracao.=" <a href=\"?op=$func&pg=$i_pg2&where=".base64_encode($where)."&ident=$ident#votacao\" class=\"next\"><b> << $limite_menor</b></a> ";
		}
		else
		{
			$inicio = 1;
			$final = 10;
		}


		for ($i_pg = $inicio; $i_pg <= $final; $i_pg++)
		{
			if($i_pg <= $quant_pg)
			{
				if (($i_pg-1) == $pg)
				{
					$numeracao .=  "<a href='#'>$i_pg</a> ";
				}
				else
				{
					$i_pg2 = $i_pg-1;
					$numeracao .= " <a href=\"?op=$func&pg=$i_pg2&where=".base64_encode($where)."&ident=$ident#votacao\" class=\"next\"><b>$i_pg</b></a> ";
				}
			}
		}

		$limite_maior = $final+1;
		
		$i_pg2 = $final;

		if($final < $quant_pg)
		{
			if($limite_maior >= $quant_pg)
			{
				$limite_maior = $quant_pg;
			}

			$numeracao.=" <a href=\"?op=$func&pg=$i_pg2&where=".base64_encode($where)."&ident=$ident#votacao\" class=\"next\"><b>$limite_maior >></b></a> ";
		}
	}
		
  
	if (($pg+2) < $quant_pg)  
		$proxima =  "<a href=\"?op=$func&pg=".($pg+1)."&where=".base64_encode($where)."&ident=$ident#votacao\" class=\"next\"><b><font> &raquo;</font></b></a>";
	else
		$proxima = "<a color=#CCCCCC href='#'> &raquo;</a>";
	
	//return array($anterior,$numeracao,$proxima,$where);
	return $where;
	
}
?>