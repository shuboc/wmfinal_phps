<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Song Recommendation System</title>
	<link href="facebox.css" media="screen" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="jquery-1.2.6.min.js"></script>
<link href="dependencies/screen.css" type="text/css" rel="stylesheet" />
<script src="jquery.elastic-1.6.js" type="text/javascript" charset="utf-8"></script>
<script src="jquery.watermarkinput.js" type="text/javascript"></script>
<script type="text/javascript">
	// <![CDATA[
	$(document).ready(function(){

		$('#shareButton').click(function(){
			var a = $("#watermark").val();
			$.post("posts.php?value="+a, {
			}, function(response){
				$('#emailInfo').fadeOut();
				$('#posting').html(unescape(response));
				$('#posting').fadeIn();
			});
		});
		$('textarea').elastic();

		jQuery(function($){
		   $("#watermark").Watermark("What's on your mind?");
		});
		jQuery(function($){
		   $("#watermark").Watermark("watermark","#369");
		});
		function UseData(){
		   $.Watermark.HideAll();
		   //Do Stuff
		   $.Watermark.ShowAll();
		}
	});
	// ]]>
</script>
</head>
<body>
	<div class='input_form' align="center">
		<h2>Song Recommendation System</h2>
		<div class="UIComposer_Box">
			<form action=myquery.php name=input_form method=post>
				<div>
					<textarea class="input" id="watermark" name="watermark" cols="46" rows="3" style="resize:none;"></textarea>
				</div>
				<div align="right" style="height:30px; padding:10px 10px;">
					<label id="shareButton" class="uiButtonLarge uiButtonShare" onclick='input_form.submit();'> Send</label>
				</div>
			</form>
		</div>
	</div>
</body>
</html>