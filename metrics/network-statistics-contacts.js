jQuery(document).ready(function(){
    let chartDiv = jQuery('#chart')
    let spinner = '<span class="loading-spinner active"></span>'

    // add highlight to menu
    jQuery('#network_statistics_contacts').prop('style', 'font-weight:900;')

    // write page layout with spinners
    chartDiv.empty().html(`
                <span class="section-header">Contacts</span>
                <hr style="max-width:100%;">
                
                <div class="grid-x callout">
                  <div class="medium-3 cell center">
                    <h4>Active Contacts<br><span id="active_contacts">${spinner}</span></h4>
                  </div>
                  <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                    <h4>Paused Contacts<br><span id="paused_contacts">${spinner}</span></h4>
                  </div>
                  <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                    <h4>Closed Contacts<br><span id="closed_contacts">${spinner}</span></h4>
                  </div>
                  <div class="medium-3 cell center" style="border-left: 1px solid #ccc">
                    <h4>Total Contacts<br><span id="total_contacts">${spinner}</span></h4>
                  </div>
                </div>
                <br>
                <div class="grid-x grid-padding-x">
                    <div class="medium-6 cell"><!--column 1-->
                        <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                          <div class="cell">
                            <span class="section-header">New Contacts </span><br>
                            <button class="button hollow new-contact-buttons" id="c-7-days" onclick="load_line_chart( 'global-contacts-chart-div', null, 'days', 7 );set_buttons('new-contact-buttons', 'c-7-days' )">Last 7 days</button> 
                            <button class="button new-contact-buttons" id="c-30-days"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'days', 30 );set_buttons('new-contact-buttons', 'c-30-days' )">Last 30 days</button> 
                            <button class="button hollow new-contact-buttons" id="c-60-days"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'days', 60 );set_buttons('new-contact-buttons', 'c-60-days' )">Last 60 days</button> 
                            <button class="button hollow new-contact-buttons" id="c-12-months"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'months', 12 );set_buttons('new-contact-buttons', 'c-12-months' )">Last 12 Months</button>
                            <button class="button hollow new-contact-buttons" id="c-24-months"  onclick="load_line_chart( 'global-contacts-chart-div', null, 'months', 24 );set_buttons('new-contact-buttons', 'c-24-months' )">Last 24 Months</button>
                            <div id="global-contacts-chart-div" style="height:500px;width:100%;">${spinner}</div>
                          </div>
                        </div>
                    </div>
                <div class="medium-6 cell"><!--column 2-->
                    <div class="grid-x grid-padding-x grid-padding-y grid-margin-y">
                  <div class="cell">
                    <span class="section-header">New Baptisms</span><br>
                    <button class="button hollow new-baptism-buttons" id="b-7-days" onclick="load_line_chart( 'global-baptisms-chart-div', null, 'days', 7 );set_buttons('new-baptism-buttons', 'b-7-days' )">Last 7 days</button> 
                    <button class="button new-baptism-buttons" id="b-30-days"  onclick="load_line_chart( 'global-baptisms-chart-div', null, 'days', 30 );set_buttons('new-baptism-buttons', 'b-30-days' )">Last 30 days</button> 
                    <button class="button hollow new-baptism-buttons" id="b-60-days"  onclick="load_line_chart( 'global-baptisms-chart-div', null, 'days', 60 );set_buttons('new-baptism-buttons', 'b-60-days' )">Last 60 days</button> 
                    <button class="button hollow new-baptism-buttons" id="b-12-months"  onclick="load_line_chart( 'global-baptisms-chart-div', null, 'months', 12 );set_buttons('new-baptism-buttons', 'b-12-months' )">Last 12 Months</button>
                    <button class="button hollow new-baptism-buttons" id="b-24-months"  onclick="load_line_chart( 'global-baptisms-chart-div', null, 'months', 24 );set_buttons('new-baptism-buttons', 'b-24-months' )">Last 24 Months</button>
                    <div id="global-baptisms-chart-div" style="height:500px;width:100%;">${spinner}</div>
                  </div>
                </div>
                </div>
                </div>
                
                <br>
                
                <div class="grid-x grid-padding-x">
                    <div class="medium-6 cell">
                          <span class="section-header">Baptism Generations</span><br>
                          <div id="generations-div" style="height:300px;width:100%;"></div>
                    </div>
                    <div class="medium-6 cell">
                    
                    </div>
                </div>
                
                <hr style="max-width:100%;">
                <div><button class="button clear" onclick="reset()">reset data</button> <span class="reset-spinner"></span></div>
            `)

    // call for data
    makeRequest('GET', 'network/base', {'type': 'global'} )
        .done(function(data) {
            window.sites = data.sites
            window.global = data.global

            jQuery('#active_contacts').html(window.global.contacts.status.active)
            jQuery('#paused_contacts').html(window.global.contacts.status.paused)
            jQuery('#closed_contacts').html(window.global.contacts.status.closed)
            jQuery('#total_contacts').html(window.global.contacts.status.total)

            load_line_chart('global-contacts-chart-div', null, 'days', 30)
            set_buttons('new-contact-buttons', 'c-30-days')

            load_line_chart('global-baptisms-chart-div', null, 'days', 30)
            set_buttons('new-baptism-buttons', 'b-30-days')

            load_global_gen_chart('generations-div', 'g-baptisms')
        })
})