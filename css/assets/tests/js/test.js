	$(document).ready(function(){
	
		var asset_path = "/css/assets/xml/layouts.xml";
		
		$(".showgrid-toggle").click(function(){
			$('#page').toggleClass('showgrid');
		});	
		
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
					
					$(this).append("<h2 class='clear'>Basic column tests</h2>");
					// Create the basic columns
					for(i=1; i<= (cc/2); i++)
					{
						
						$(this).append("<div class='columns-"+i+"'>"+((i * (cw+gw)) - gw) +"px</div>");
						if(i != cc)
						{
							$(this).append("<div class='columns-"+(cc - i)+" last'>"+ (((cc - i) * (cw+gw)) - gw)+"px</div>");
						}
					}
					
					$(this).append("<h2 class='clear'>Single column float tests</h2>");
					// Create the basic columns
					for(i=1; i<=cc; i++)
					{
						if(i == cc)
						{
							$(this).append("<div class='columns-1 last'></div>");
						}
						else
						{
							$(this).append("<div class='columns-1'></div>");
						}
					}
					
					$(this).append("<h2 class='clear'>Columns pushed forward</h2>");
					// Create the pushed columns
					for(i=1; i<cc; i++)
					{
						$(this).append("<div class='columns-1 push-"+i+" clear'>Column pushed "+i+"</div>");
					}
					
					$(this).append("<h2 class='clear'>Columns with appended columns</h2>");
					// Create the appended columns
					for(i=1; i<cc; i++)
					{
						$(this).append("<div class='columns-1 append-"+i+" clear'>Column with "+i+" columns appended as padding</div>");
					}
					
					$(this).append("<h2 class='clear'>Columns prepended with columns</h2>");
					// Create the prepended columns
					for(i=1; i<cc; i++)
					{
						$(this).append("<div class='columns-1 prepend-"+i+" clear'>Column with "+i+" columns prepended as padding</div>");
					}
					
					$(this).append("<h2 class='clear'>Heights set with baseline-x</h2>");
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