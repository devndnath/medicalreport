See the project doc "claude/workbook-analysis-and-schema.md" in the "york medicine" Claude project for the full write-up (workbook structure, data quality issues, schema rationale). Summary:

- 23 medicines, 14 real historical issue rows (2024-11-02/03 only), opening balances for a handful of items — no price/supplier/batch/expiry data anywhere in the source file.
- Valuation policy: FIFO. Historical costs with no source price import as "Cost unavailable", never fabricated.
- Department name variants normalized (Maintance -> Maintenance) while preserving the original label; "Q" department kept as-is pending factory confirmation of its meaning.
- "Sry.Entacyd Plus" (syrup) and "Entacyd Plus-(400mg)" (tablet) are two distinct medicine master records, not merged.
