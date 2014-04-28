<?php
require_once '/../model/ManageDB.php';

class Graphique{
	
	/**
	 * @var ManageDB
	 */
	private $db;
	
	function construct(){	
		$this->db = new ManageDB();
	}
	/**
	 * Permet de generer un graphique en camembert.
	 * @param array $data
	 * Tableau associatif dont la clé représente le nom du secteur à crée et la valeur représente son pourcentage.
	 * @param string $divId 
	 * Le nom du div où le graphique va apparaitre.
	 * @param string $title
	 *  Le titre du diagramme.
	 */
	public function genererGraphique($data,$divId,$title){
		?>
		
		<script type="text/javascript" src="/report/retrievecourse/lib/jqplot/dist/jquery.jqplot.min.js"></script>
		<link rel='stylesheet' type="text/css" href="/report/retrievecourse/lib/jqplot/dist/jquery.jqplot.min.css"/>
		<script type="text/javascript" src="/report/retrievecourse/lib/jqplot/dist/plugins/jqplot.pieRenderer.min.js"></script>
		 <script>

		 	 var <?php echo $divId; ?> = new Array();
			 var ind = 0;
		     <?php 
				foreach ($data as $key => $pourcent ) {
			?>
				<?php echo $divId; ?>[ind] = [<?php echo json_encode($key); ?>, <?php echo $pourcent; ?>];
		        ind += 1;
			 <?php } ?> 
		 
		
		$(document).ready(function(){
			    var plot1 = jQuery.jqplot ('<?php echo $divId; ?>', [<?php echo $divId; ?>], 
			    { 
				  title:'<?php echo $title; ?>',  
			      seriesDefaults: {
			        renderer: jQuery.jqplot.PieRenderer, 
			        rendererOptions: {
			          showDataLabels: true
			        }
			      }, 
			      legend: { show:true, location: 'e' }
			    }
			  );
			});
</script>
		<?php 
		
	}
	

}