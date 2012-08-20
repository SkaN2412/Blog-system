jQuery.fn.setHints = function()
{
	return this.each(function()
	{
		switch ($(this).val())
		{
			case "":
				$(this).val($(this).attr("title"));
				break;
			case $(this).attr("title"):
				$(this).val("");
				break;
			default:
				break;
		}
	});
};