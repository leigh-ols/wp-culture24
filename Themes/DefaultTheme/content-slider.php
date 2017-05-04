<?php
    //Feat head
    $op= '<div id="flexslider-events" class="flexslider c24 events">
        <ul data-orbit class="slides">';


    // Slides
    foreach($c24objects as $c24event) {
        //Slide head
        $op.= '<li data-orbit-slide="slide-event-'.$c24event->get_event_id().'" class="slide clearfix fix"><div class="slide-cont fix">';

        $c24img = $c24event->get_image_url_large();
        $c24title = $c24event->get_name();
        $c24url = "/events/?c24event=".$c24event->get_event_id().'#c24events';
        $c24venue = $c24event->get_venue_name();
        $c24type = $c24event->get_type();
        $c24location = $c24event->get_location_string();
        //@TODO check up on this 'instance' mallarky,.. when is there more than one?
        $c24sdate = $c24event->get_date_start(0);
        $c24edate = $c24event->get_date_end(0);
        $c24date = $c24sdate;
        if($c24sdate != $c24edate) {
            $c24date = $c24sdate. ' - ' . $c24edate;
        }
        $c24charges = trim($c24event->get_charges());

        if(!$c24charges && $c24event->get_free())
        {
            $c24charges = 'Free';
        }
        // Slide content
        $op.= '<div class="feat-img" data-fit="0" data-velocity="-.15">';
        if($c24url) {
            $op.='<a href="'.$c24url.'" title="event">';
        }
        $op.='<img class="feat__image scrolly" src="'.$c24img.'" alt="'.$c24title.'"/>';
        if($c24url) {
            $op.='</a>';
        }
        $op.='</div>';
        $op.= '<div class="feat-text">
        <div class="c24event__details">';
        $op.='<h2>Upcoming event...</h2>';
        if($c24url) {
            $op.='<a href="'.$c24url.'" title="event">';
        }
        $op.='<h3 class="c24event__title">'.$c24title.'</h3>';
        if($c24url) {
            $op.='</a>';
        }
        $op.='<!-- <h4 class="c24event__venue-name">'.$c24venue.'</h4> -->

            <h3 class="c24event__date">'.$c24date.'</h3>
            <h3 class="c24event__location">'.$c24location.'</h3>
            <span class="c24event__type">'.$c24type.'</span>
            <span class="c24event__charges">'.$c24charges.'</span>
        </div>';
        // Slide foot
        $op.='</div></div></li>';
    }
    $op.='
        </ul>
        </div>
 <script>$("#flexslider-events").flexslider({"animation":"slide","slideshowSpeed":"7000","pauseOnAction":true,"pauseOnHover":false,"controlNav":false,"directionNav":false,"controlsContainer":"#flexslider-52 .controls"}); </script>';
    echo $op;
