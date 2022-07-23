var foco = 0;
/*Función que edita celda*/
function editaCelda( flag, num, prefix = null, onblur_function = null ){
	if(foco==1){//validamos que no sea la misma celda
		return false;
	}
//formamos la caja de texto
	input_tmp='<input type="text" class="form-control" id="entrada_temporal" onkeyup="valida_acc(event,'+flag+','+num+');"' 
		+ ' onblur="';
		input_tmp += ( onblur_function != null ? '' + onblur_function + ';' : '' );// ' ,\'' + onblur_function + '\'' : ''
		input_tmp += 'deseditaCelda('+flag+','+num; 
		if( prefix != null ){
			input_tmp += (',\''+prefix+'\'');
		} 
	input_tmp += ');';
input_tmp += '">';
//extraemos el valor de la celda
	antes=$("#"+ ( prefix == null ? '' : prefix ) +flag+"_"+num).html();
	$("#"+ ( prefix == null ? '' : prefix ) +flag+"_"+num).html(input_tmp);
	$("#entrada_temporal").val(antes);
	$("#entrada_temporal").select();
	foco=1;
}

/*Función que desedita celda*/
function deseditaCelda( flag, num, prefix = null, onblur_function = null ){
	temporal=$("#entrada_temporal").val();
	$("#"+ ( prefix == null ? '' : prefix ) +flag+"_"+num).html(temporal);
	foco=0;
//realizamos acciones dependiendo la caja de texto
	var subtotal=0,porcentaje_desc=0,subtotal_desc=0,total=0;
	if( ( flag==4||flag==5||flag==6||flag==7||flag==11 ) && prefix == null ){
	//cajas recibidas
		porcentaje_desc=$("#11_"+num).html();
		subtotal=parseFloat(($("#5_"+num).html().trim()*$("#4_"+num).html().trim())+parseFloat($("#6_"+num).html().trim()));
		subtotal_desc=subtotal*porcentaje_desc;
		total=subtotal-subtotal_desc;
	
		$("#9_"+num).html(subtotal);
//		$("#8_"+num).html(Math.round(total*parseFloat($("#7_"+num).html().trim()),2) );
		$("#8_"+num).html(Number((total*parseFloat($("#7_"+num).html().trim())).toFixed(2)));		
	}
	
	/*implementacion Oscar 06.09.2019 para no dejar recibir mas piezas de las pendientes spor recibir*/
		//alert($("#3_"+num).html()+"|"+$("#9_"+num).html());
		/*
DESAHBILITADO POR OSCAR 2022
		if( parseFloat($("#3_"+num).html())<parseFloat($("#9_"+num).html()) ){
			alert("No se pueden recibir mas piezas de las pendientes por recibir!!!");//\nPendientes:"+$("#3_"+num).html()+"\nRecibidas"+$("#9_"+num).html()
			$("#"+flag+"_"+num).html(0);
			$("#"+flag+"_"+num).click();
			return false;
		}*/
	/*Fin de cambio Oscar 06.09.2019*/
	if( (flag == 16 || flag == 17) && prefix == 'pp_' ){
		change_product_provider_price( num, ( flag == 16 ? 2 : 3 ) );
	}
 
//mandamos el cambio por ajax
	if(flag==-1||flag==11){
		var fl_tmp='';
		
		if(flag==-1){fl_tmp='ubicacion';}
		if(flag==11){fl_tmp='descuento';}

		var val_id=$("#1_"+num).html();
		
		$.ajax({
			type:'post',
			url:'recPedBD.php',
			cache:false,
			data:{flag:fl_tmp,valor:temporal,id:val_id},
			success:function(dat){
				if(dat!='ok'){
					alert("Error al modificar la ubicacion del almacen en Matriz!!!"+dat);
					return false;
				}
			}
		});
	}
}

/*función que quita fila*/
function quitar_fila( num, type = null ){
	var url = '', flag = '', delete_id = '', remove_prefix = '', is_tmp = 0;
	if( type == 'recepcion_detalle' ){
		url = 'ajax/db.php';
		flag = 'deleteOrderDetail';
		delete_id = $( '#0_' + num ).html().trim();
		remove_prefix = "#fila_";
		//marcamos el check correspondiente
		document.getElementById("10_"+num).checked=true;
	//lanza emergente de opciones de fila
	}else if( type == 'measure' ){
		url = "ajax/getProductProvider.php";
		flag = 'deleteMeasure';
		delete_id = $( '#measures_1_' + num ).html().trim();
		alert( delete_id );
		remove_prefix = "#measure_row_";
		if( $( '#measures_18_' + num ).html().trim() == 1 ){
			is_tmp = 1;
		}
	}
//ocultamos la fila
	if( !confirm( "Eliminar fila?" ) ){
		return false;
	}
	$.ajax({
		type : 'post',
		url : url,
		cache : false,
		data : { fl : flag, id : delete_id, is_temporal : is_tmp },
		success : function ( dat ){
			var aux = dat.split( '|' );
			if( aux[0] != 'ok' ){
				alert( dat );
				return false;
			}
			$( remove_prefix + num).remove();
			foco=0;//reseteamos el enfoque
			return true;
		}
	});
}

/*Función que valida acción en la celda*/
function valida_acc(e,flag,num){
	var tca=e.keyCode;
	var tope=$("#filas_totales").val();//sacamos el tamaño del grid

//si es tecla abajo o intro
	if(tca==40||tca==13){
		if(num==tope){
			$("#"+flag+"_"+num).select();
			return false;
		}
		$("#input_buscador").focus();
		$("#"+flag+"_"+parseInt(num+1)).click();
	}
//si es tecla arriba 
	if(tca==38){
		if(num==1){
			$("#"+flag+"_"+num).select();
			return false;
		}
		$("#input_buscador").focus();
		$("#"+flag+"_"+parseInt(num-1)).click();
	}
//si es tecla derecha
	if(tca==39){
		if(flag==11){
			$("#"+flag+"_"+num).select();
			return false;
		}
		$("#input_buscador").focus();
		if(flag<7){
			$("#"+parseInt(flag+1)+"_"+num).click();
		}else if(flag==7){
			$("#11_"+num).click();
		}
	}
//si es tecla izquierda 
	if(tca==37){
		if(flag==4){
			$("#"+flag+"_"+num).select();
			return false;
		}
		$("#input_buscador").focus();
		if(flag==11){
			$("#7_"+num).click();
		}else{
			$("#"+parseInt(flag-1)+"_"+num).click();
		}
	}

}