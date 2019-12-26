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
                            <h1 style="color:#5D2DDC;font-family:Arial,helvetica,sans-serif;font-size:24px;line-height:35px;font-weight:bold;margin-bottom:20px;margin-top:20px;margin-left:20px;margin-right:20px"> Hello {{ $data["name"] }}</h1>
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
                                        </h2>
                                        <p style="color:#666666;font-family:Arial,helvetica,sans-serif;font-size:12px;font-weight:normal;margin-bottom:20px;margin-top:0px">

                                            Thanks for applying on {{ $data["job_title"] }} job of {{ $data["company_name"] }}. The recruiter will shortly see your Resume and will contact you on chat. Please stay online on {{ $data["fairname"] }} virtual open day.
                                        </p>
                                        <h3 style="color:#5D2DDC;font-family:Arial,helvetica,sans-serif;font-size:15px;line-height:28px;font-weight:bold;margin-bottom:10px">

                                        </h3>

                                        <p style="color:#666666;font-family:Arial,helvetica,sans-serif;font-size:12px;font-weight:normal;margin-bottom:20px;margin-top:0px">
                                            If you have any questions, please contact us at <a href="mailto:info@virtualrecruitmentdays.com" style="color:#0083be;text-decoration:none" target="_blank">info@virtualrecruitmentdays.com</a>.
                                        </p>
                                        <p style="color:#666666;font-family:Arial,helvetica,sans-serif;font-size:12px;font-weight:normal;margin-bottom:20px;margin-top:0px">
                                            Good Luck and See you at the event
                                            <br>
                                            Best Regards
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
                                <small style="font-size:9px">
                                    <a href="{{ url('/emails/unsubscribe/'.$data['faircandidate_id'].'/'.$data['candidate_id']) }}"> Unsubscribe</a>
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
