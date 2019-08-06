<?php
$prlx = $this->config->get('react_slider_react_slider');
if(is_array($content)) {
    //$this->log->write(print_r($content, true).'    ');
    $count = 1;
    ?>
<style>
h3.slider__slide-subheading {<?php echo "color :#".$this->config->get('react_slider_colour_2');?>}
h2.slider__slide-heading{<?php echo "color :#".$this->config->get('react_slider_colour_1');?>}
.slider__slide-readmore{<?php echo "color :#".$this->config->get('react_slider_colour_3');?>}
</style>
<div id="app" style="<?php if ($this->config->get('react_slider_fullwidth')) {echo 'width:100vw;';
}?>">
   <div data-reactroot="" class="slider s--ready">
      <img src="<?php if($prlx) { echo 'resources/'.$prlx;
     }?>">
      <div class="slider__control"></div>
      <div class="slider__control slider__control--right"></div>
   </div>
</div>
<script type="text/babel">
class CitiesSlider extends React.Component {
     constructor(props) {
       super(props);

       this.IMAGE_PARTS = 4;

       this.changeTO = null;
       <?php if($this->config->get('react_slider_time') > 500) {
         $AUTOCHANGE_TIME = (int)$this->config->get('react_slider_time');
       } else {
         $AUTOCHANGE_TIME = 4000;
       }
       ?>
       this.AUTOCHANGE_TIME = <?php echo $AUTOCHANGE_TIME; ?>;

       this.state = { activeSlide: -1, prevSlide: -1, sliderReady: false };
     }

     componentWillUnmount() {
       window.clearTimeout(this.changeTO);
     }

     componentDidMount() {
       this.runAutochangeTO();
       setTimeout(() => {
         this.setState({ activeSlide: 0, sliderReady: true });
       }, 0);
     }

     runAutochangeTO() {
       this.changeTO = setTimeout(() => {
         this.changeSlides(1);
         this.runAutochangeTO();
       }, this.AUTOCHANGE_TIME);
     }

     changeSlides(change) {

       window.clearTimeout(this.changeTO);
       const { length } = this.props.slides;
       const prevSlide = this.state.activeSlide;
       let activeSlide = prevSlide + change;
       if (activeSlide < 0) activeSlide = length - 1;
       if (activeSlide >= length) activeSlide = 0;
       this.setState({ activeSlide, prevSlide });
     }

     render() {
       const { activeSlide, prevSlide, sliderReady } = this.state;
       return (
         <div className={classNames('slider', { 's--ready': sliderReady })}>
           <p className="slider__top-heading"> </p>
           <div className="slider__slides">
             {this.props.slides.map((slide, index) => (
               <div
                 className={classNames('slider__slide', { 's--active': activeSlide === index, 's--prev': prevSlide === index  })}
                 key={slide.keyforimage}
                 >
                 <div className="slider__slide-content">
                  <?php if ($this->config->get('react_slider_alllink')) { ?><a class="myablock" href={slide.url}><?php } ?>
                    <h3 className="slider__slide-subheading">{slide.seohiddentext || slide.keyforimage}</h3>
                    <h2 className="slider__slide-heading">
                      {slide.keyforimage.split('&nbsp;').map(l => <span>{l}</span>)}
                    </h2>
                   <?php if (!$this->config->get('react_slider_alllink')) { ?>
                   <a class="myablock" href={slide.url}><p className="slider__slide-readmore"><?php echo $this->language->get('react_slider_text_more'); ?></p></a>
                   <?php } ?>
                   <?php if ($this->config->get('react_slider_alllink')) { ?></a><?php } ?>
                 </div>
                 <div className="slider__slide-parts">
                   {[...Array(this.IMAGE_PARTS).fill()].map((x, i) => (
                     <div className="slider__slide-part" key={i}>
                       <div className="slider__slide-part-inner" style={{ backgroundImage: `url(${slide.img})` }} />
                     </div>
                   ))}
                 </div>
               </div>
             ))}
           </div>
           <div className="slider__control" onClick={() => this.changeSlides(-1)} />
           <div className="slider__control slider__control--right" onClick={() => this.changeSlides(1)} />
         </div>
       );
     }
   }

   const slides = [
    <?php

    if (count($content) > 1) {

            for ($i=0; $i < count($content) ; $i++) {
            if($content[$i]['banner_group_name'] == 'React Image Slider banners'){
            $string = strip_tags(html_entity_decode($content[$i]['meta'], ENT_COMPAT, 'UTF-8'));
            $image_text = str_replace(array("\n","\r"), '', $string);
            $image_text = mb_substr($image_text, 0, 80);
            ?>
             {
                 keyforimage: '<?php echo html_entity_decode($content[$i]['name'], ENT_COMPAT, 'UTF-8'); ?>', 
                 seohiddentext: '<?php echo $image_text;?>', 
                 url: '<?php echo html_entity_decode($content[$i]['target_url'], ENT_COMPAT, 'UTF-8'); ?>',
                 img: '<?php echo html_entity_decode($content[$i]['images']['0']['main_url'], ENT_COMPAT, 'UTF-8'); ?>',
             },
    <?php   }
            }

    } ?>

   ];
   ReactDOM.render(<CitiesSlider slides={slides} />, document.querySelector('#app'));
</script>
<script type="text/javascript">
$(document).ready(function () {

    var bowps = $('#app').outerWidth();
    //console.log(bowps);

    var wwps = $(window).width();
    //console.log(wwps);

    var swps = $('#app').parent().width();
    //console.log(swps);

    <?php if ($this->config->get('react_slider_fullwidth')) { ?>
  	//fix padding problem for full width only
  	if(wwps-swps < 70){
  		$('#app').css({
  				'margin-left': -(bowps - swps)/2 + 'px',
  				//'margin-right': -(bowps - swps)/2 + 'px',
  				//'left':'50%'
  		});


  	}
    <?php }
    //column width fix
    if (!$this->config->get('react_slider_fullwidth')) { ?>
      if(wwps-swps > 70){
        $("<style type='text/css'>.slider__slide-part{ width: " + bowps/4 + "px;} .slider__slide{ width: 100%;} .slider__slide-part-inner, .slider__slide-part-inner:before { width: " + bowps + "px;}</style>").appendTo("head");
        /*$('.slider__slide-part').css( "width", bowps/4 + 'px');
        $('.slider__slide').css("width", "100%");
        $('.slider__slide-part-inner').css("width", bowps + 'px');
        $('.slider__slide-part-inner:before').css("width", bowps + 'px');*/
        //console.log('new css');
      }
    <?php } ?>




    //remove width if added to block8
    /*if( $('#app').parent().prop('className') == 'block_8'){
        $('#app').css({
                'width': 'auto',
        });
    }*/

});












$(window).on('load', function() {

 //column fix

});
</script>
<?php } ?>
