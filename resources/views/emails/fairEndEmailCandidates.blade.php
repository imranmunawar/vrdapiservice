<html style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; box-sizing: border-box; font-size: 14px; margin: 0;">
<head>

</head>

<body style="font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 14px; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; height: 100%; line-height: 1.6em; background-color: #efebe3; margin: 0;" bgcolor="#efebe3">

<div style="width:100%;margin:0px;background-color:#efebe3">

    <table bgcolor="#efebe3" border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%;background-color:#efebe3">
        <tbody>
        <tr>
            <td align="center" valign="top">
                <table border="0" cellspacing="0" cellpadding="0" width="600" align="center" style="color:#6666666;line-height:20px;font-size:12px;font-family:Arial,helvetica,sans-serif;text-align:left">
                    <tbody><tr>
                        <td height="30" valign="top" colspan="3">&nbsp;</td>
                    </tr>
                    <tr class="m_-7922248444950610321up-header-lvl-00">
                        <td width="20" valign="top">&nbsp;</td>
                        <!--<td width="560" align="center"><a href=""><img src="https://virtualrecruitmentdays.com/wp-content/uploads/2017/12/vrdlogo2.png" alt="Virtual Recruitment Days" height="100" border="0" class="CToWUd"></a></td>-->
                        <td width="20" valign="top">&nbsp;</td>
                    </tr>

                    <tr class="m_-7922248444950610321up-header-lvl-01">
                        <td height="30" valign="top" colspan="3">&nbsp;</td>
                    </tr>

                    <tr class="m_-7922248444950610321up-header-lvl-02">
                        <td bgcolor="#FFFFFF" valign="top" width="600" colspan="3">
                            <h1 style="color:#5D2DDC;font-family:Arial,helvetica,sans-serif;font-size:24px;line-height:35px;font-weight:bold;margin-bottom:20px;margin-top:20px;margin-left:20px;margin-right:20px">Dear {{ $name }}</h1>
                        </td>
                    </tr>

                    <tr class="m_-7922248444950610321up-header-lvl-03">
                        <td valign="top" colspan="3">&nbsp;</td>
                    </tr>

                    <tr class="m_-7922248444950610321content">
                        <td colspan="3" width="600" valign="top">
                            <table border="0" cellspacing="0" cellpadding="0" width="600" align="center" bgcolor="#ffffff">
                                <tbody><tr>
                                    <td colspan="3">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="20" valign="top">&nbsp;</td>
                                    <td valign="top" width="560" style="font-family:Arial,helvetica,sans-serif;font-weight:normal;font-size:12px;line-height:19px;color:#666666">
                                        <h2 style="color:#5D2DDC;font-family:Arial,helvetica,sans-serif;font-size:18px;line-height:28px;font-weight:bold;margin-bottom:10px">
                                            {{ $fair_name }} Fair is ending at 17:00 Today.
                                        </h2>
                                        <p style="color:#666666;font-family:Arial,helvetica,sans-serif;font-size:12px;font-weight:normal;margin-bottom:20px;margin-top:0px">
                                            Click the button below and participate to speak with hiring managers directly
                                        </p>
                                        <table border="0" cellpadding="0" cellspacing="0" style="background-color:#5D2DDC;border-radius:2px;margin-bottom:20px">
                                            <tbody>
                                                <tr>
                                                    <td align="center" valign="middle" style="color:#ffffff;font-family:Helvetica,Arial,sans-serif;font-size:14px;font-weight:bold;line-height:150%;padding-top:5px;padding-right:10px;padding-bottom:5px;padding-left:10px">
                                                        <a style="color:#fff;text-decoration:none" href="{{ url('/', $fair_id) }}" target="_blank">Login to Fair</a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <h3 style="color:#5D2DDC;font-family:Arial,helvetica,sans-serif;font-size:15px;line-height:28px;font-weight:bold;margin-bottom:10px">
                                            Available jobs for you
                                        </h3>
                                        
                                        @if(!empty($jobs))
                                           <div style="margin: 20px 0; border-top: 1px solid #e1e1e1; -webkit-border-radius: 3px 3px 0 0 !important; -moz-border-radius: 3px 3px 0 0 !important; -ms-border-radius: 3px 3px 0 0 !important; -o-border-radius: 3px 3px 0 0 !important; border-radius: 3px 3px 0 0 !important;">
                                               @forelse($jobs as $key=>$job) @if($job->percentage >= $job->jobDetail->match)
                                               <div style="height: 85px; transition: all 0.6s ease 0s; -webkit-transition: all 0.6s ease 0s; -moz-transition: all 0.6s ease 0s; -o-transition: all 0.6s ease 0s; -ms-transition: all 0.6s ease 0s; -webkit-backface-visibility: hidden; padding: 15px; background: #ffffff; border-bottom: 1px solid #e1e1e1; border: 1px solid #e1e1e1; border-top: 0;">
                                                   <div style="margin-right: -15px; margin-left: -15px; display: inline-flex; margin-bottom: -12px;">
                                                       <div style="display: block !important; width: 16.66666667%; float: left; position: relative; min-height: 1px; padding-right: 15px; padding-left: 15px;">
                                                           <div style="-webkit-border-radius: 3px !important; -moz-border-radius: 3px !important; -ms-border-radius: 3px !important; -o-border-radius: 3px !important; border-radius: 3px !important; text-align: center; max-width: 100%;">
                                                               <img src="http://virtualrecruitmentdays.com/front/images/job.png" alt="Job Icon" width="80" style="vertical-align: middle; border: 0;">
                                                           </div>
                                                       </div>
                                                       <div style="position: relative; min-height: 1px; padding-right: 15px; padding-left: 15px; width: 83.33333333%;">
                                                           <div style="margin-right: -15px; margin-left: -15px; display: inline-flex; width: 100%;">
                                                               <div style="position: relative; min-height: 1px; padding-right: 15px; padding-left: 15px; width: 98%;">
                                                                   <h4 style="margin-top: 0 !important;">
                                                                       <a href="{{ url('/'.$fair_id.'/job-jobDetail', $job->job_id) }}" style="overflow: hidden; float: left; font-weight: bold; text-align: justify; font-size: 15px;">{{ $job->jobDetail->title }}</a>
                                                                   </h4>
                                                               </div>
                                                           </div>
                                                           <div style="margin-right: -15px; margin-left: -15px; display: inline-flex; width: 100%">
                                                               <div style="position: relative; min-height: 1px; padding-right: 15px; padding-left: 15px; width: 100%">
                                                                   <h5 style="margin: 0px !important;">
                                                                       <span style="color: #000000 !important;">{{ $job->jobDetail->job_type }}</span>
                                                                        -
                                                                       <span style="color: #b9b9b9 !important;">{{$job->jobDetail->location }}</span>
                                                                   </h5>
                                                               </div>
                                                           </div><br>
                                                           <div style="margin-right: -15px; margin-left: -15px; display: inline-flex; width: 100%">
                                                               <div style="position: relative; min-height: 1px; padding-right: 15px; padding-left: 15px;  width: 100%;">
                                                                   <h5 style="margin: 0px !important;">
                                                                       <span style="color: #000000 !important;">Match</span>
                                                                        -
                                                                       <span style="color: #b9b9b9 !important;">{{ $job->percentage }}%</span>
                                                                   </h5>
                                                               </div>
                                                           </div>
                                                       </div>
                                                   </div>
                                               </div>
                                               <!-- end item list -->
                                               @endif @empty
                                               <p> No Job Found</p>
                                               @endforelse
                                           </div>
                                       
                                        @endif


                                        <p style="color:#666666;font-family:Arial,helvetica,sans-serif;font-size:12px;font-weight:normal;margin-bottom:20px;margin-top:0px">
                                            If you have any questions, please contact us at <a href="mailto:info@virtualrecruitmentdays.com" style="color:#0083be;text-decoration:none" target="_blank">info@virtualrecruitmentdays.com</a>.
                                        </p>
                                        <p style="color:#666666;font-family:Arial,helvetica,sans-serif;font-size:12px;font-weight:normal;margin-bottom:20px;margin-top:0px">
                                            Thank you,
                                            <br>
                                            Virtual Recruitment Team
                                        </p>
                                    </td>
                                    <td width="20" valign="top">&nbsp;</td>
                                </tr>
                            </tbody></table>
                        </td>
                    </tr>
                </tbody></table>
                <table width="600" cellspacing="0" cellpadding="0" border="0">
                    <tbody><tr>
                        <td>
                            &nbsp;
                        </td>
                    </tr>
                    <tr class="m_-7922248444950610321debug">
                        <td>
                            <p style="text-align:center;color:#616161;font-size:9px;line-height:15px;font-family:Arial,Verdana,sans-serif">
                                <img src="https://virtualrecruitmentdays.com/wp-content/uploads/2017/12/vrdlogo2.png" alt="Virtual Recruitment Days" style="width:100px;height:auto" class="CToWUd"><br><br>
                                © Copyright {{ date('Y') }} Virtual Recruitment Days. All rights reserved.
                                <br>
                                United Kingdom
                                <br>
                                <small style="font-size:9px">
                                    <a href="https://virtualrecruitmentdays.com/contact">Contact Us</a>
                                    <span>|</span>
                                    <a href="https://virtualrecruitmentdays.com/privacy-policy/"> Privacy Policy</a>
                                </small>
                                <br>
                                <br>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            &nbsp;
                        </td>
                    </tr>

                </tbody></table>
            </td>
        </tr>
        </tbody>
    </table>

</div>
</body>
</html>
