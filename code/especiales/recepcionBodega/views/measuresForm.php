<?php
	//echo  'id :' . $row['box_lenght'];
?>
<div class="group_card">
	<h5>
		<input type="checkbox" id="no_box_measures" 
			<?php
				echo ( (isset($row['tmp_id'] ) && 
						( $row['box_lenght'] != 0 || $row['box_width'] != 0 || $row['box_height'] != 0 ) ) || !isset($row['tmp_id'] ) ? ' checked' : '' );
			?>
			onclick="disabled_enabled_box_measures();"> 
		Medidas de la caja
	</h5>
	<div class="row" id="box_measures_container">
		<div class="col-4">
			<input type="number" id="box_lenght"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['box_lenght'] != 0 ?  " value=\"{$row['box_lenght']}\"" : '' );
			?>
			class="form-control">
			<label for="box_lenght" class="measures_label">Largo (cm)</label>
		</div>
		<div class="col-4">
			<input type="number" id="box_width"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['box_width'] != 0 ?  " value=\"{$row['box_width']}\"" : '' );
			?>
			class="form-control">
			<label class="measures_label">Ancho (cm)</label>
		</div>
		<div class="col-4">
			<input type="number" id="box_height"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['box_height'] != 0 ?  " value=\"{$row['box_height']}\"" : '' );
			?> 
			class="form-control">
			<label class="measures_label">Alto (cm)</label>
		</div>
	</div>
<hr>
	
	<h5>
		<input type="checkbox" id="no_pack_measures" 
			<?php
				echo ( ( isset($row['tmp_id'] ) && 
						( $row['pack_lenght'] != 0 || $row['pack_width'] != 0 || $row['pack_height'] != 0 ) ) || !isset($row['tmp_id'] ) ? ' checked' : '' );
			?> 
			onclick="disabled_enabled_pack_measures();"> 
		Medidas del paquete
	</h5>

	<div class="row" style="font-size:90%;">
		<div class="row" id="takePhotoContainer">
			<div class="col-4">
				<input type="number" id="pack_lenght"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['pack_lenght'] != 0 ?  " value=\"{$row['pack_lenght']}\"" : '' );
			?>
				class="form-control">
				<label class="measures_label">Largo (pzas)</label>
			</div>
			<div class="col-4">
				<input type="number" id="pack_width"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['pack_width'] != 0 ?  " value=\"{$row['pack_width']}\"" : '' );
			?>
				class="form-control">
				<label class="measures_label">Ancho (pzas)</label>
			</div>
			<div class="col-4">
				<input type="number" id="pack_height"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['pack_height'] != 0 ?  " value=\"{$row['pack_height']}\"" : '' );
			?>  
				class="form-control">
				<label class="measures_label">Alto (pzas)</label>
			</div>
			<br><br>
			<div class="col-3 text-center" style="padding-top : 8px;">
				<b>Bolsa :</b>
			</div>
			<div class="col-9">
				<?php echo getComboPackBags( $link, ( isset( $row['pack_bag_id'] ) ? $row['pack_bag_id'] : null ) ); ?>
			</div>

			<?php
				include( '../../plugins/takePhoto.php' );
			?>
		    <br>
	    <!-- -->
		    <div id="options_buttons">
		        <!--button type="button" onclick="open_camera()" class="btn btn-info form-control">
		            <i class="icon-instagram" id="camera_btn">Abrir Camara</i>
		        </button-->
		        <button type="button" id="boton" onclick="takeScreen( '#video_container', '#img_1', 'ajax/db.php?fl=savePhoto', '', 1 )" class="btn btn-success form-control">
		            <i class="icon-picture-outline">Tomar foto</i>
		        </button>
		        <p id="estado">
		        </p>
		        <div class="row">
		            <div class="col-4" onclick="set_global_photo_render( 1, 'open_box.png' )">
		                <img <?php
								echo ( isset($row['tmp_id'] ) && $row['image_1'] != '' ?  "src=\"../../../files/packs_img_tmp/{$row['image_1']}\"" : "src=\"../../../img/frames/camera_icon.jpeg\"" );
							?> id="previous_img_1" width="100%">
		                <p align="center" style="color : blue; font-size : 90%;">Caja Abierta</p>
		            </div>
		            <div class="col-4" onclick="set_global_photo_render( 2, 'length_height.png' )">
		                <img <?php
								echo ( isset($row['tmp_id'] ) && $row['image_2'] != '' ?  "src=\"../../../files/packs_img_tmp/{$row['image_2']}\"" : "src=\"../../../img/frames/camera_icon.jpeg\"" );
							?> id="previous_img_2" width="100%">
		                <p align="center" style="color : blue; font-size : 90%;">Frontal</p>
		            </div>
		            <div class="col-4" onclick="set_global_photo_render( 3, 'length_width.png' )">
		                
		                <img <?php
								echo ( isset($row['tmp_id'] ) && $row['image_3'] != '' ?  "src=\"../../../files/packs_img_tmp/{$row['image_3']}\"" : "src=\"../../../img/frames/camera_icon.jpeg\"" );
							?> id="previous_img_3" width="100%">
		                <p align="center" style="color : blue; font-size : 90%;">Lateral</p>
		            </div>
		        </div>
		    </div>
		</div>
	</div>
	
	<hr>
	
	<h5>
		<input type="checkbox" id="no_piece_measures" 
			<?php
				echo ( ( isset($row['tmp_id'] ) && 
						( $row['piece_lenght'] != 0 || $row['piece_width'] != 0 || $row['piece_height'] != 0
							|| $row['piece_weight'] != 0 ) ) || !isset($row['tmp_id'] ) ? ' checked' : '' );
			?> 
			onclick="disabled_enabled_piece_measures();"> 
		Medidas de la pieza
	</h5>
	<div class="row" id="piece_measures_container" style="font-size:90%;">
		<div class="col-3">
			<input type="number"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['piece_lenght'] != 0 ?  " value=\"{$row['piece_lenght']}\"" : '' );
			?>  
				 id="piece_lenght" class="form-control">
			<label class="measures_label">Largo (cm)</label>
		</div>
		<div class="col-3">
			<input type="number"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['piece_width'] != 0 ?  " value=\"{$row['piece_width']}\"" : '' );
			?>  
				 id="piece_width" class="form-control">
			<label class="measures_label">Ancho (cm)</label>
		</div>
		<div class="col-3">
			<input type="number"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['piece_height'] != 0 ?  " value=\"{$row['piece_height']}\"" : '' );
			?>  
				 id="piece_height" class="form-control">
			<label class="measures_label">Alto (cm)</label>
		</div>
		<div class="col-3">
			<input type="number"
			<?php
				echo ( isset($row['tmp_id'] ) && $row['piece_weight'] != 0 ?  " value=\"{$row['piece_weight']}\"" : '' );
			?>  
				 id="piece_weight" class="form-control">
			<label class="measures_label">Peso (kg)</label>
		</div>
	</div>

	<br>

	<div class="row">
		<div class="col-1"></div>
		<div class="col-5">
			<button class="btn btn-success form-control" onclick="save_measures( <?php echo ( isset($row['tmp_id'] ) ?  $row['tmp_id'] : '' );
			 ?> );">
				Guardar
			</button>
		</div>

		<div class="col-5">
			<button class="btn btn-danger form-control" onclick="close_emergent();">
				Cerrar
			</button>
		</div>
		<div class="col-1"></div>
	</div>
</div>

<style type="text/css">
	.measures_label{
		font-size: 60%;
		color: red;
	}
</style>