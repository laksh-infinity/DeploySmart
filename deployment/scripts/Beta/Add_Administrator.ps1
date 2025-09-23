Add-LocalGroupMember -Group "Administrators" -Member "user.domain.se\GroupForAdmins"
Remove-LocalGroupMember -Group "Administrators" -Member "USER\Domain Admins"
net localgroup Administrators "USER\Domain Admins" /delete