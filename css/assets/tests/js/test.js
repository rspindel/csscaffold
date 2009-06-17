	$(document).ready(function(){
	
		var asset_path = "/css/assets/xml/layouts.xml";
		
		$.ajax({
        	type: "GET",
			url: asset_path,
			dataType: "xml",
			success: function(xml) {
			
				// Add the layouts to the options menu
 				$(xml).find('layout').each(function(){
 					$("#layout-options").append("<option>" + $(this).text() + "</option>");
				});
				
				// Get everything for the grid tests
				cc = parseInt($(xml).find('column-count').text());
				cw = parseInt($(xml).find('column-width').text());
				gw = parseInt($(xml).find('gutter-width').text());
				
				// Create the columns in the grid tests
				$("#page.grid").each(function(){
					
					// Create the basic columns
					for(i=1; i<= (cc/2); i++)
					{
						
						$(this).append("<div class='columns-"+i+"'>"+((i * (cw+gw)) - (gw*2)) +"px</div>");
						if(i != cc)
						{
							$(this).append("<div class='columns-"+(cc - i)+"'>"+ (((cc - i) * (cw+gw)) - (gw*2))+"px</div>");
						}
					}
					
					// Create the pushed columns
					for(i=1; i<cc; i++)
					{
						$(this).append("<div class='columns-1 push-"+i+" clear'>Column pushed "+i+"</div>");
					}
					
					// Create the appended columns
					for(i=1; i<cc; i++)
					{
						$(this).append("<div class='columns-1 append-"+i+" clear'>Column with "+i+" columns appended as padding</div>");
					}
					
					// Create the prepended columns
					for(i=1; i<cc; i++)
					{
						$(this).append("<div class='columns-1 prepend-"+i+" clear'>Column with "+i+" columns prepended as padding</div>");
					}
				
					// Create the columns with baseline heights
					for(i=1; i<=10; i++)
					{
						$(this).append("<div class='columns-"+cc+" baseline-"+i+" clear'>Height is "+i+" baselines</div>");
					}

				});
			}
		});
		
		// Add the default layout to the page
		$("#page").addClass('layout-default');
		
		// Change the page class when option is selected
		$("#layout-options").change(function(){
			
			// Remove all classes
			$("#page").removeClass();
			
			// Get the new class
			$("#layout-options option:selected").each(function(){
				newClass = $(this).text();
			});
						
			// Add the class
			$("#page").addClass(newClass);
			
		});
		
	});