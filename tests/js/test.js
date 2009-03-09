	$(document).ready(function(){
	
		$(".showgrid-toggle").click(function(){
			$('#page').toggleClass('showgrid');
		});	
		
		$.ajax({
        	type: "GET",
			url: "../../assets/xml/layouts.xml",
			dataType: "xml",
			success: function(xml) {
 				$(xml).find('layout').each(function(){
 					$("#layout-options").append("<option>" + $(this).text() + "</option>");
				});
				
			}
		});
		
		$("#page").addClass('layout-default');
		
		$("#layout-options").change(function(){
		
			// Check if the grid is on
			hasGrid = $("#page").hasClass('showgrid');
			
			// Remove all classes
			$("#page").removeClass();
			
			// Get the new class
			$("#layout-options option:selected").each(function(){
				newClass = $(this).text();
			});
						
			// Add the class
			$("#page").addClass(newClass);
			
			// If the grid was on, turn it back on
			if(hasGrid)
			{
				$("#page").addClass('showgrid');
			}
			
		});
		
	});