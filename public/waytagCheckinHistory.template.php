<li><span class="history_waytag_reference"><?=$history_entry["waytag_reference"]?></span><a href="#" onclick="panMapToWayTag('<?=$history_entry["waytag_id"];?>');"><img src="images/locate.png" /></a><br /> 
<span class="history_checked_in_at"><?=$history_entry["checked_in_at"]?></span> - <span class="history_checked_out_at"><?= ($history_entry["checked_out_at"]?$history_entry["checked_out_at"]:"not yet checked out")?> <?=$history_entry["checked_out_at"]?"":"<button style='button' onclick='checkOut(\"".$history_entry["waytag_id"]."\");'>Checkout</button>"?></span>
</li>
