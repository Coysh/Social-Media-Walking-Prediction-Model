<div class="container">
	<div class="row">
        <div class="col-md-12">
            <form action="#" method="get">
                <div class="input-group">
                    <!-- USE TWITTER TYPEAHEAD JSON WITH API TO SEARCH -->
                    <input class="form-control" id="system-search" name="q" placeholder="Search for" required>
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-default"><i class="glyphicon glyphicon-search"></i></button>
                    </span>
                </div>
            </form>
        </div>
	</div>
	<div class="row">
		<div class="col-md-12">
    	 <table class="table table-list-search">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Number of Crimes</th>
                        </tr>
                    </thead>
                    <tbody>
						<?php
						$stmt = $resultsdb->prepare("SELECT * FROM `$user` ORDER BY num_crimes DESC ");
						$stmt->execute();
						$i=0;
						while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							echo "<tr><td>".date('F, Y',strtotime($row['month']))."</td>
							<td>".$row['location']."</td>
							<td>".$row['num_crimes']."</td></tr>";
						}						
						?>
                    </tbody>
                </table>   
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    var activeSystemClass = $('.list-group-item.active');

    //something is entered in search form
    $('#system-search').keyup( function() {
       var that = this;
        // affect all table rows on in systems table
        var tableBody = $('.table-list-search tbody');
        var tableRowsClass = $('.table-list-search tbody tr');
        $('.search-sf').remove();
        tableRowsClass.each( function(i, val) {
        
            //Lower text for case insensitive
            var rowText = $(val).text().toLowerCase();
            var inputText = $(that).val().toLowerCase();
            if(inputText != '')
            {
                $('.search-query-sf').remove();
                tableBody.prepend('<tr class="search-query-sf"><td colspan="6"><strong>Searching for: "'
                    + $(that).val()
                    + '"</strong></td></tr>');
            }
            else
            {
                $('.search-query-sf').remove();
            }

            if( rowText.indexOf( inputText ) == -1 )
            {
                //hide rows
                tableRowsClass.eq(i).hide();
                
            }
            else
            {
                $('.search-sf').remove();
                tableRowsClass.eq(i).show();
            }
        });
        //all tr elements are hidden
        if(tableRowsClass.children(':visible').length == 0)
        {
            tableBody.append('<tr class="search-sf"><td class="text-muted" colspan="6">No entries found.</td></tr>');
        }
    });
});
</script>