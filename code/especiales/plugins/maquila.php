<?php
	class maquila
	{
		private $link;
		function __construct( $connection )
		{
			$this->link = $connection;
		}

		public function make_form( $product_id, $quantity = 0, $function_js ){
			$sql = "SELECT
						pd.id_producto AS product_origin_id,
						p1.nombre AS name_origin,
						pd.id_producto_ordigen AS product_destinity_id,
						p2.nombre AS name_destinity,
						pd.cantidad AS quantity
					FROM ec_productos_detalle pd
					LEFT JOIN ec_productos p1 ON p1.id_productos = pd.id_producto
					LEFT JOIN ec_productos p2 ON p2.id_productos = pd.id_producto_ordigen";
			$stm = $this->link->query( $sql ) or die( "Error al consultar datos de la maquila : {$this->link->error}" );
			$row = $stm->fetch_assoc();
			$resp = "<script type=\"text/JavaScript\">
					function calculation_maquila_form(){
						var maquila_response = 0;
						var maquila_complete = 0;
						var maquila_pieces = 0;
						var maquila_equivalent = 0;
						maquila_complete = ( document.getElementById('maquila_complete').value <= 0 ? 0 : document.getElementById('maquila_complete').value );
						maquila_pieces = ( document.getElementById('maquila_pieces').value <= 0 ? 0 : document.getElementById('maquila_pieces').value );
						maquila_equivalent = ( document.getElementById('maquila_equivalent').value <= 0 ? 0 : document.getElementById('maquila_equivalent').value );
						var maquila_response = parseInt(maquila_complete) + ( maquila_equivalent * maquila_pieces );
						document.getElementById('maquila_decimal').value = maquila_response;
					}

			</script>";
			if( $quantity != 0 ){
				$quantity_complete = floor( $quantity );/// $row['quantity']
				//$quantity_pieces = ( $quantity % ( 1 / $row['quantity'] ) ) * ( $row['quantity'] );
				$quantity_pieces = ( $quantity - $quantity_complete ) * ( $row['quantity'] ) ;
			}
			$resp .= "<div class=\"row\">";
				$resp .= "<div class=\"col-12\">";
					$resp .= "<h6>Una unidad de {$row['name_destinity']} equivale a " . ( 1 / $row['quantity'] ) . " de {$row['name_origin']}";
					$resp .= "<input type=\"hidden\" value=\"" . ( 1 / $row['quantity'] ) . "\" id=\"maquila_equivalent\">";
				$resp .= "</div>";
				$resp .= "<div class=\"col-6\">
						<p>Ingresa las presentaciones completas <br>( rollo, caja, paquete, etc ): </p>
						<input type=\"number\" value=\"{$quantity_complete}\" id=\"maquila_complete\" onkeyup=\"calculation_maquila_form();\" class=\"form-control\">
					</div>";
				$resp .= "<div class=\"col-6\">
						<p>Ingresa las piezas sueltas <br>( metro, pieza, etc ): </p>
						<input type=\"number\" value=\"{$quantity_pieces}\" id=\"maquila_pieces\" onkeyup=\"calculation_maquila_form();\" class=\"form-control\">
					</div>";
				$resp .= "<div class=\"col-4\">
						<input type=\"hidden\" readonly id=\"maquila_decimal\" value=\"{$quantity}\">
					</div>";
				$resp .= "<div class=\"col-4\">
							<button type=\"button\" class=\"btn btn-success form-control\" onclick=\"{$function_js}\">Aceptar</button>
					</div>";
			$resp .= "</div>";	

			return $resp;
		}
	}
?>