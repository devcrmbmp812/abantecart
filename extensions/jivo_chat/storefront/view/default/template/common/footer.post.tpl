<?php if ($this->config->get('page_a') == 0 & $this->config->get('hide_mobile') == 0)
{ echo $jivo_chat_code;
  echo '<script type="text/javascript">
(function(){ var widget_id = "' .$this->config->get('jivo_chat_code'). '";var d=document;var w=window;function l(){
var s = document.createElement("script"); s.type = "text/javascript"; s.async = true; s.src = "//code.jivosite.com/script/widget/"+widget_id; var ss = document.getElementsByTagName("script")[0]; ss.parentNode.insertBefore(s, ss);}if(d.readyState=="complete"){l();}else{if(w.attachEvent){w.attachEvent("onload",l);}else{w.addEventListener("load",l,false);}}})();
</script>';}
elseif ($this->config->get('page_a') == 0 & $this->config->get('hide_mobile') == 1)
{echo '<script type="text/javascript">
if( !(navigator.userAgent.match(/Android/i)
 || navigator.userAgent.match(/webOS/i)
 || navigator.userAgent.match(/iPhone/i)
 || navigator.userAgent.match(/iPad/i)
 || navigator.userAgent.match(/iPod/i)
 || navigator.userAgent.match(/BlackBerry/i)
 || navigator.userAgent.match(/Windows Phone/i)
 )) {
   (function(){ var widget_id = "' .$this->config->get('jivo_chat_code'). '";var d=document;var w=window;function l(){
   var s = document.createElement("script"); s.type = "text/javascript"; s.async = true; s.src = "//code.jivosite.com/script/widget/"+widget_id; var ss = document.getElementsByTagName("script")[0]; ss.parentNode.insertBefore(s, ss);}if(d.readyState=="complete"){l();}else{if(w.attachEvent){w.attachEvent("onload",l);}else{w.addEventListener("load",l,false);}}})();
  }
</script>';}
?>
